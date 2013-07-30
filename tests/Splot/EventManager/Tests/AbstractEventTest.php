<?php
namespace Splot\EventManager\Tests;

use Splot\EventManager\AbstractEvent;
use Splot\EventManager\EventManager;

use Splot\EventManager\Tests\TestFixtures\Event;

class AbstractEventTest extends \PHPUnit_Framework_TestCase
{

    protected $eventManager;

    public function setUp() {
        $this->eventManager = new EventManager();
    }

    public function testSetGetEventManager() {
        $event = new Event();
        $event->setEventManager($this->eventManager);
        $this->assertEquals($this->eventManager, $event->getEventManager());
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testSetEventManagerInvalid() {
        $event = new Event();
        $event->setEventManager(new \stdClass());
    }

    public function testStopPropagation() {
        $event = new Event();
        $this->assertFalse($event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testPreventDefault() {
        $event = new Event();
        $this->assertFalse($event->isDefaultPrevented());
        $event->preventDefault();
        $this->assertTrue($event->isDefaultPrevented());
    }

    public function testName() {
        $this->assertInternalType('string', Event::getName());
    }

}
