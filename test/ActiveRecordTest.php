<?php

namespace wp_activerecord;

require '../ActiveRecord.php';
require 'wpdbMock.php';

class Table extends ActiveRecord {
    protected static $table_name = 'table';
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
        $wpdb->sql = null;
        $this->active_record = new Table();
    }

    /**
     * @covers wp_activerecord\ActiveRecord::save
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
     * @covers wp_activerecord\ActiveRecord::save
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
     * @covers wp_activerecord\ActiveRecord::save_pre
     */
    public function testSave_pre() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\Table', ['save_pre']);
        $this->active_record
            ->expects($this->once())
            ->method('save_pre');
        
        $this->active_record->property = 'value';
        $this->active_record->save();
    }
    
    /**
     * @covers wp_activerecord\ActiveRecord::save_post
     */
    public function testSave_post() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\Table', ['save_post']);
        $this->active_record
            ->expects($this->once())
            ->method('save_post');
        
        $this->active_record->property = 'value';
        $this->active_record->save();
    }

    /**
     * @covers wp_activerecord\ActiveRecord::delete
     */
    public function testDeleteNew() {
        global $wpdb;
        $this->active_record->delete();
        $this->assertNull($wpdb->sql);
    }
    
    /**
     * @covers wp_activerecord\ActiveRecord::delete
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
     * @covers wp_activerecord\ActiveRecord::delete_pre
     */
    public function testDelete_pre() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\Table', ['delete_pre']);
        $this->active_record
            ->expects($this->once())
            ->method('delete_pre');
        
        $this->active_record->id = 9;
        $this->active_record->delete();
    }
    
    /**
     * @covers wp_activerecord\ActiveRecord::delete_post
     */
    public function testDelete_post() {
        global $wpdb;
        $this->active_record = $this->getMock('\\wp_activerecord\\Table', ['delete_post']);
        $this->active_record
            ->expects($this->once())
            ->method('delete_post');
        
        $this->active_record->id = 9;
        $this->active_record->delete();
    }

    /**
     * @covers wp_activerecord\ActiveRecord::get_table_name
     * @todo   Implement testGet_table_name().
     */
    public function testGet_table_name() {
        $this->assertEquals(
            'prefix_table',
            Table::get_table_name()
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::wpdb
     */
    public function testWpdb() {
        global $wpdb;
        $this->assertEquals(
            $wpdb,
            ActiveRecord::wpdb()
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::delete_by_id
     * @todo   Implement testDelete_by_id().
     */
    public function testDelete_by_id() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::get
     * @todo   Implement testGet().
     */
    public function testGet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::get_row_by_id
     * @todo   Implement testGet_row_by_id().
     */
    public function testGet_row_by_id() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::get_row
     * @todo   Implement testGet_row().
     */
    public function testGet_row() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::get_var
     * @todo   Implement testGet_var().
     */
    public function testGet_var() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::__set
     * @todo   Implement test__set().
     */
    public function test__set() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers wp_activerecord\ActiveRecord::__get
     * @todo   Implement test__get().
     */
    public function test__get() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
    
    /**
     * @covers wp_activerecord\ActiveRecord::__callStatic
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
     * @covers wp_activerecord\ActiveRecord::__callStatic
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
     * @covers wp_activerecord\ActiveRecord::__callStatic
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

}
