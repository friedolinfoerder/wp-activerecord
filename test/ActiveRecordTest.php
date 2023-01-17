<?php

namespace wp_activerecord\testing;

use wp_activerecord\ActiveRecord;

class Table extends ActiveRecord {
    protected static $table_name = 'table';

    protected static $casts = [
        'count' => 'int'
    ];
}

/**
 * Test for ActiveRecord
 */
class ActiveRecordTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var ActiveRecord
     */
    protected $active_record;

    protected function setUp() {
        global $wpdb;
        // create global mock
        $wpdb = new wpdbMock();

        $this->active_record = new Table();
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::save
     */
    public function testSaveNew() {
        global $wpdb;
        $this->active_record->property = 'value';
        $this->active_record->save();

        $this->assertEquals(
            "INSERT INTO `prefix_table` \n"
          . "(`property`) VALUES ('value')",
            $wpdb->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::save
     */
    public function testSaveExisting() {
        global $wpdb;
        $this->active_record->id = 9;
        $this->active_record->property = 'value';
        $this->active_record->save();

        $this->assertEquals(
            "UPDATE `prefix_table` \n"
          . "SET `id` = '9', `property` = 'value' \n"
          . "WHERE ( `id` = '9' )",
            $wpdb->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::save_pre
     */
    public function testSave_pre() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\testing\\Table', ['save_pre']);
        $this->active_record
            ->expects($this->once())
            ->method('save_pre');

        $this->active_record->property = 'value';
        $this->active_record->save();
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::save_post
     */
    public function testSave_post() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\testing\\Table', ['save_post']);
        $this->active_record
            ->expects($this->once())
            ->method('save_post');

        $this->active_record->property = 'value';
        $this->active_record->save();
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::delete
     */
    public function testDeleteNew() {
        global $wpdb;
        $this->active_record->delete();
        $this->assertNull($wpdb->sql);
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::delete
     */
    public function testDeleteExisting() {
        global $wpdb;
        $this->active_record->id = 9;
        $this->active_record->delete();

        $this->assertEquals(
            "DELETE FROM `prefix_table` \n"
          . "WHERE ( `id` = '9' )",
            $wpdb->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::delete_pre
     */
    public function testDelete_pre() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\testing\\Table', ['delete_pre']);
        $this->active_record
            ->expects($this->once())
            ->method('delete_pre');

        $this->active_record->id = 9;
        $this->active_record->delete();
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::delete_post
     */
    public function testDelete_post() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\testing\\Table', ['delete_post']);
        $this->active_record
            ->expects($this->once())
            ->method('delete_post');

        $this->active_record->id = 9;
        $this->active_record->delete();
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::get_table_name
     */
    public function testGet_table_name() {
        $this->assertEquals(
            'prefix_table',
            Table::get_table_name()
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::get
     */
    public function testGet() {
        Table::get(3);

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `prefix_table` \n"
          . "WHERE ( `id` = '3' )",
            Table::wpdb()->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::get
     */
    public function testGetNonExisting() {
        global $wpdb;
        $wpdb->resultsReturn = null;
        $result = Table::get(3);

        $this->assertSame(
            null,
            $result
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::get
     */
    public function testGetExisting() {
        global $wpdb;
        $wpdb->rowReturn = ['id' => 3];
        $result = Table::get(3);

        $this->assertSame(
            3,
            $result->id
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::get
     */
    public function testGetAll() {
        Table::get();

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `prefix_table`",
            Table::wpdb()->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::wpdb
     */
    public function testWpdb() {
        global $wpdb;
        $this->assertEquals(
            $wpdb,
            ActiveRecord::wpdb()
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::__callStatic
     */
    public function test__callStatic() {
        global $wpdb;
        Table::get_one_by_id(1);

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `prefix_table` \n"
          . "WHERE ( `id` = '1' )",
            $wpdb->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::__callStatic
     */
    public function test__callStaticMuliple() {
        global $wpdb;
        Table::get_one_by_name_and_category_id('technic', 12);

        $this->assertEquals(
            "SELECT * \n"
          . "FROM `prefix_table` \n"
          . "WHERE ( `name` = 'technic' AND `category_id` = '12' )",
            $wpdb->sql
        );
    }

    /**
     * @covers \wp_activerecord\ActiveRecord::__callStatic
     */
    public function test__callStaticGetVar() {
        global $wpdb;
        Table::get_var_name_by_id(2);

        $this->assertEquals(
            "SELECT name \n"
          . "FROM `prefix_table` \n"
          . "WHERE ( `id` = '2' )",
            $wpdb->sql
        );
    }


    public function testCastsGetVarNumber() {
        global $wpdb;
        $wpdb->varReturn = '5';
        $result = Table::get_var_count_by_id(2);

        $this->assertSame(
            5,
            $result
        );
    }


    public function testCastsGetRowNumber() {
        global $wpdb;
        $wpdb->rowReturn = ['count' => '5'];
        $result = Table::get_one_by_id(2);

        $this->assertSame(
            5,
            $result->count
        );
    }


    public function testCastsGetColNumber() {
        global $wpdb;
        $wpdb->colReturn = ['5'];
        $result = Table::get_col_count();

        $this->assertSame(
            5,
            $result[0]
        );
    }


    public function testCastsGetNumber() {
        global $wpdb;
        $wpdb->resultsReturn = [['count' => '5']];
        $result = Table::get();

        $this->assertSame(
            5,
            $result[0]->count
        );
    }


    public function testCastsGetNullIfNotExists() {
        global $wpdb;
        $wpdb->resultsReturn = [['count' => '5']];
        $result = Table::get();

        $this->assertSame(
            null,
            $result[0]->id
        );
    }
}
