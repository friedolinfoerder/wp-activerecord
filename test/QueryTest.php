<?php

namespace wp_activerecord\testing;

use wp_activerecord\Query;

/**
 * Test for Query Class
 */
class QueryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Query
     */
    protected $query;

    protected function setUp() {
        global $wpdb;
        // create global mock
        $wpdb = new wpdbMock();

        $this->query = new Query('table');
    }

    /**
     * @covers \wp_activerecord\Query::select
     */
    public function testSelectSimple() {
        $query = new Query();
        $this->assertEquals(
            "SELECT RAND()",
            $query->select('RAND()')->sql()
        );
    }

    /**
     * @covers \wp_activerecord\Query::select
     */
    public function testSelectFrom() {
        $query = $this->query;
        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table`",
            $this->query->select()->sql()
        );
    }

    /**
     * @covers \wp_activerecord\Query::select
     */
    public function testSelectAttributes() {
        $query = $this->query;
        $this->assertEquals(
            "SELECT id, name \n"
          . "FROM `table`",
            $this->query->select('id', 'name')->sql()
        );
    }

    /**
     * @covers \wp_activerecord\Query::select
     */
    public function testSelectChained() {
        $this->assertEquals(
            "SELECT id, name \n"
          . "FROM `table`",
            $this->query->select('id')->select('name')->sql()
        );
    }

    /**
     * @covers \wp_activerecord\Query::delete
     */
    public function testDelete() {
        $preparation = $this->query
            ->delete()
            ->prepare();

        $this->assertEquals(
            "DELETE FROM `table`",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::set
     */
    public function testSetOneAttribute() {
        $preparation = $this->query
            ->set("name", "john")
            ->prepare();

        $this->assertEquals(
            "UPDATE `table` \n"
          . "SET `name` = %s",
            $preparation->sql
        );
        $this->assertEquals(
            ["john"],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::set
     */
    public function testSetTwoAttributes() {
        $preparation = $this->query
            ->set("name", "john")
            ->set("age", 23)
            ->prepare();

        $this->assertEquals(
            "UPDATE `table` \n"
          . "SET `name` = %s, `age` = %s",
            $preparation->sql
        );
        $this->assertEquals(
            ["john", 23],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::set
     */
    public function testSetNull() {
        $preparation = $this->query
            ->set("name", null)
            ->prepare();

        $this->assertEquals(
            "UPDATE `table` \n"
          . "SET `name` = NULL",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::set
     */
    public function testSetRaw() {
        $preparation = $this->query
            ->set("name", ["RAND()"])
            ->prepare();

        $this->assertEquals(
            "UPDATE `table` \n"
          . "SET `name` = RAND()",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::set
     */
    public function testSetMultiple() {
        $preparation = $this->query
            ->set(['name' => 'john', 'age' => 33])
            ->prepare();

        $this->assertEquals(
            "UPDATE `table` \n"
          . "SET `name` = %s, `age` = %s",
            $preparation->sql
        );
        $this->assertEquals(
            ['john', 33],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::insert
     */
    public function testInsert() {
        $data = [
            'name' => 'john',
            'age' => ['RAND()'],
            'parent_id' => null
        ];
        $preparation = $this->query
            ->insert($data)
            ->prepare();

        $this->assertEquals(
            "INSERT INTO `table` \n"
          . "(`name`, `age`, `parent_id`) VALUES (%s, RAND(), NULL)",
            $preparation->sql
        );
        $this->assertEquals(
            ['john'],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::insert
     */
    public function testInsertMultiple() {
        $data = [
            ['name' => 'john', 'age' => 37],
            ['age' => 22, 'name' => 'jim']
        ];
        $preparation = $this->query
            ->insert($data)
            ->prepare();

        $this->assertEquals(
            "INSERT INTO `table` \n"
          . "(`name`, `age`) VALUES (%s, %s), (%s, %s)",
            $preparation->sql
        );
        $this->assertEquals(
            ['john', 37, 'jim', 22],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereEquals() {
        $preparation = $this->query
            ->where("name", "john")
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `name` = %s )",
            $preparation->sql
        );
        $this->assertEquals(
            ["john"],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereGreaterAs() {
        $preparation = $this->query
            ->where("age", ">", 23)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `age` > %s )",
            $preparation->sql
        );
        $this->assertEquals(
            [23],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereIsNull() {
        $preparation = $this->query
            ->where("parent_id", null)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
           . "FROM `table` \n"
           . "WHERE ( `parent_id` IS NULL )",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereIsNotNull() {
        $preparation = $this->query
            ->where("parent_id", "is not", null)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `parent_id` IS NOT NULL )",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereIn() {
        $list = [1, 2, 3];
        $preparation = $this->query
            ->where("list", "in", [$list])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `list` IN (%s, %s, %s) )",
            $preparation->sql
        );
        $this->assertEquals(
            $list,
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereMultiple() {
        $preparation = $this->query
            ->where(['age' => null, 'name' => 'john'])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `age` IS NULL AND `name` = %s )",
            $preparation->sql
        );
        $this->assertEquals(
            ['john'],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereRawString() {
        $preparation = $this->query
            ->where('age = 38')
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( age = 38 )",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::where
     */
    public function testWhereRawArray() {
        $preparation = $this->query
            ->where(['age = %s', 38])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( age = %s )",
            $preparation->sql
        );
        $this->assertEquals(
            [38],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::and_where
     */
    public function testAnd_where() {
        $preparation = $this->query
            ->where("name", "john")
            ->and_where("age", "<", 18)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `name` = %s AND `age` < %s )",
            $preparation->sql
        );
        $this->assertEquals(
            ["john", 18],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::or_where
     */
    public function testOr_where() {
        $preparation = $this->query
            ->where("name", "john")
            ->or_where("age", "<", 18)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "WHERE ( `name` = %s ) OR ( `age` < %s )",
            $preparation->sql
        );
        $this->assertEquals(
            ["john", 18],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::group_by
     */
    public function testGroup_by() {
        $preparation = $this->query
            ->group_by('age', 'desc')
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "GROUP BY `age` DESC",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::group_by
     */
    public function testGroup_byRaw() {
        $preparation = $this->query
            ->group_by(['RAND()'])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "GROUP BY RAND()",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::having
     */
    public function testHaving() {
        $preparation = $this->query
            ->having("value", "<", ["RAND()"])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "HAVING ( `value` < RAND() )",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::and_having
     */
    public function testAnd_having() {
        $preparation = $this->query
            ->having("value", "is not", null)
            ->and_having("value", ">", ["RAND()"])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "HAVING ( `value` IS NOT NULL AND `value` > RAND() )",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::or_having
     */
    public function testOr_having() {
        $preparation = $this->query
            ->having("value", "something")
            ->or_having("value", ">", ["RAND()"])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "HAVING ( `value` = %s ) OR ( `value` > RAND() )",
            $preparation->sql
        );
        $this->assertEquals(
            ["something"],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::having
     */
    public function testHavingRawColumn() {
        $preparation = $this->query
            ->group_by('name')
            ->having(["SUM(price)"], ">", 10)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "GROUP BY `name` ASC \n"
          . "HAVING ( SUM(price) > %s )",
            $preparation->sql
        );
        $this->assertEquals(
            [10],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::order_by
     */
    public function testOrder_by() {
        $preparation = $this->query
            ->order_by('age')
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "ORDER BY `age` ASC",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::limit
     */
    public function testLimit() {
        $preparation = $this->query
            ->limit(5)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "LIMIT %d",
            $preparation->sql
        );
        $this->assertEquals(
            [5],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::limit
     */
    public function testLimitRaw() {
        $preparation = $this->query
            ->limit(['RAND()'])
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "LIMIT RAND()",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::offset
     */
    public function testOffset() {
        $preparation = $this->query
            ->offset(10)
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table` \n"
          . "LIMIT 18446744073709551615 \n"
          . "OFFSET %d",
            $preparation->sql
        );
        $this->assertEquals(
            [10],
            $preparation->vars
        );
    }

    /**
     * @covers \wp_activerecord\Query::sql
     * @todo   Implement testSql().
     */
    public function testSqlWithSimpleQuery() {
        $this->assertEquals(
            "SELECT * \n"
          . "FROM `table`",
            $this->query->sql()
        );
    }

    /**
     * @covers \wp_activerecord\Query::sql
     */
    public function testSqlWithAdvancedQuery() {
        global $wpdb;

        $expectedObject = new \stdClass();

        // create global mock
        $wpdb = $this->getMock('\\wp_activerecord\\wpdbMock', ['prepare']);
        $wpdb->expects($this->once())
             ->method('prepare')
             ->with($this->equalTo("SELECT * \n"
                                 . "FROM `table` \n"
                                 . "LIMIT %d"),
                    $this->equalTo(5))
             ->will($this->returnValue($expectedObject));

        $this->assertEquals(
            $expectedObject,
            $this->query->limit(5)->sql()
        );

    }

    /**
     * @covers \wp_activerecord\Query::join
     */
    public function testJoinSimple() {
        $preparation = $this->query
            ->join('table2', 'id', 'foreign_id')
            ->prepare();

        $this->assertEquals(
            "SELECT * \n"
            . "FROM `table` \n"
            . "INNER JOIN `table2` ON `table`.`id` = `table2`.`foreign_id`",
            $preparation->sql
        );
        $this->assertEquals(
            [],
            $preparation->vars
        );
    }

}
