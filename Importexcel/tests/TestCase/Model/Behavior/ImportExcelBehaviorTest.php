<?php
namespace ImportExcel\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use ImportExcel\Model\Behavior\ImportExcelBehavior;

/**
 * ImportExcel\Model\Behavior\ImportExcelBehavior Test Case
 */
class ImportExcelBehaviorTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \ImportExcel\Model\Behavior\ImportExcelBehavior
     */
    public $ImportExcel;

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->ImportExcel = new ImportExcelBehavior();
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->ImportExcel);

        parent::tearDown();
    }

    /**
     * Test initial setup
     *
     * @return void
     */
    public function testInitialization()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
