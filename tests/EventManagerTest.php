<?php
namespace Splot\EventManager\Tests;

use Splot\EventManager\EventManager;

use Splot\EventManager\Tests\TestFixtures\DummyEvent;
use Splot\EventManager\Tests\TestFixtures\Event;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testSubscribe() {
        $eventManager = new EventManager();
        $listenerCalled = false;
        $eventManager->subscribe('lipsum', function() use ($listenerCalled) {
            $listenerCalled = true;
        });
    }

    public function provideInvalidListener() {
        return array(
            array(array()),
            array(123),
            array('string'),
            array(array($this, 'nonExistentMethod')),
            array(new \stdClass())
        );
    }

    /**
     * @dataProvider provideInvalidListener
     * @expectedException \InvalidArgumentException
     */
    public function testSubscribeInvalid($listener) {
        $eventManager = new EventManager();
        $eventManager->subscribe('lipsum', $listener);
    }

    public function testTriggeringSubscribedListener() {
        $event = new Event();
        $eventManager = new EventManager();

        $listenerCalled = false;
        $phpunit = $this;
        $eventManager->subscribe(Event::getName(), function($ev) use (&$listenerCalled, $phpunit, $event) {
            $phpunit->assertEquals($event, $ev);
            $listenerCalled = true;
        });

        $eventManager->trigger($event);

        $this->assertTrue($listenerCalled);
    }

    public function testTriggeringOrder() {
        $eventManager = new EventManager();

        $listenerCallOrder = array();
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'c';
        });
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'b';
        }, 10);
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'e';
        }, -1);
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'a';
        }, 11);
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'd';
        });
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCallOrder) {
            $listenerCallOrder[] = 'f';
        }, -999);

        $eventManager->trigger(new Event());

        $this->assertEquals(array('a', 'b', 'c', 'd', 'e', 'f'), $listenerCallOrder);
    }

    public function testTriggeringNoListeners() {
        $eventManager = new EventManager();

        $listenerCalled = false;
        $eventManager->subscribe(Event::getName(), function() use (&$listenerCalled) {
            $listenerCalled = true;
        });

        $eventManager->trigger(new DummyEvent());

        $this->assertFalse($listenerCalled);
    }

    public function testPreventingDefault() {
        $eventManager = new EventManager();

        // via method call
        $event = new Event();
        $eventManager->subscribe(Event::getName(), function($ev) {
            $ev->preventDefault();
        });
        $eventManager->trigger($event);
        $this->assertTrue($event->isDefaultPrevented());
    }

    public function testPreventingDefaultByReturningFalse() {
        $eventManager = new EventManager();

        // via method call
        $event = new Event();
        $eventManager->subscribe(Event::getName(), function($ev) {
            return false;
        });
        $eventManager->trigger($event);
        $this->assertTrue($event->isDefaultPrevented());
    }

    public function testTriggerReturningFalseWhenDefaultPrevented() {
        $eventManager = new EventManager();

        // via method call
        $event = new Event();
        $eventManager->subscribe(Event::getName(), function($ev) {
            $ev->preventDefault();
        });
        $defaultPrevented = $eventManager->trigger($event);
        $this->assertFalse($defaultPrevented);
    }

    public function testStopPropagation() {
        $eventManager = new EventManager();

        $event = new Event();
        $eventManager->subscribe(Event::getName(), function($ev) {
            $ev->stopPropagation();
        });
        $eventManager->trigger($event);
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testStopTriggeringListenersWhenPropagationStopped() {
        $eventManager = new EventManager();

        $listenersCalled = array();
        $eventManager->subscribe(Event::getName(), function($ev) use (&$listenersCalled) {
            $listenersCalled[] = 'first';
        });
        $eventManager->subscribe(Event::getName(), function($ev) use (&$listenersCalled) {
            $listenersCalled[] = 'second';
            $ev->stopPropagation();
        });
        $eventManager->subscribe(Event::getName(), function($ev) use (&$listenersCalled) {
            $listenersCalled[] = 'third';
        });

        $eventManager->trigger(new Event());

        $this->assertEquals(array('first', 'second'), $listenersCalled);
    }

    public function testUnsubscribe() {
        $event = new Event();
        $eventManager = new EventManager();

        $eventManager->unsubscribe('undefined', function() {});

        $subscribedListenerCalled = false;
        $subscribedListener = function() use (&$subscribedListenerCalled) {
            $subscribedListenerCalled = true;
        };
        $eventManager->subscribe(Event::getName(), $subscribedListener);

        $unsubscribedListenerCalled = false;
        $unsubscribedListener = function() use (&$unsubscribedListenerCalled) {
            $unsubscribedListenerCalled = true;
        };
        $eventManager->subscribe(Event::getName(), $unsubscribedListener);
        $eventManager->unsubscribe(Event::getName(), $unsubscribedListener);

        $eventManager->trigger($event);

        $this->assertTrue($subscribedListenerCalled);
        $this->assertFalse($unsubscribedListenerCalled);
    }

    public function testGetListeners() {
        $eventManager = new EventManager();
        $this->assertInternalType('array', $eventManager->getListeners());
    }

    public function testGetListenersForEvent() {
        $eventManager = new EventManager();

        $this->assertEquals(array(), $eventManager->getListenersForEvent(Event::getName()));

        $listeners = array(
            function() {
                echo 'first listener';
            },
            function() {
                echo 'second listener';
            },
            function() {
                echo 'third listener';
            }
        );

        foreach($listeners as $listener) {
            $eventManager->subscribe(Event::getName(), $listener);
        }

        $this->assertEquals($listeners, $eventManager->getListenersForEvent(Event::getName()));
    }

}
