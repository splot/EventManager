<?php
/**
 * Event Manager.
 * 
 * @package SplotEventManager
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\EventManager;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use MD\Foundation\Debug\Debugger;
use MD\Foundation\Utils\ArrayUtils;

use Splot\EventManager\AbstractEvent;

class EventManager
{

    /**
     * Holds list of subscribed listeners.
     * 
     * @var array
     */
    private $_listeners = array();

    /**
     * Logger for events.
     * 
     * @var Logger
     */
    private $_logger;

    /**
     * Constructor.
     * 
     * @param LoggerInterface $logger [optional] Logger into which info about called events will be sent.
     */
    public function __construct(LoggerInterface $logger = null) {
        $this->_logger = $logger ? $logger : new NullLogger();
    }

    /**
     * Triggers an event and all its listeners.
     * 
     * @param AbstractEvent $event Event to be triggered.
     * @return bool Was default behavior prevented?
     */
    public function trigger(AbstractEvent $event) {
        $name = call_user_func(array(Debugger::getClass($event), 'getName'));
        $event->setEventManager($this);

        if (!isset($this->_listeners[$name])) {
            $this->_listeners[$name] = array();
        }

        $this->_logger->info('Triggered event "{name}" with {count} listeners.', array(
            'name' => $name,
            'count' => count($this->_listeners[$name])
        ));

        foreach($this->_listeners[$name] as $i => $listener) {
            $preventDefault = call_user_func_array($listener['callable'], array($event));
            if ($preventDefault === false) {
                $event->preventDefault();
                $this->_logger->info('Default prevented of event "{name}" at listener #{i} - "{listener}".', array(
                    'name' => $name,
                    'i' => $i,
                    'listener' => Debugger::callableToString($listener['callable'])
                ));
            }

            if ($event->isPropagationStopped()) {
                $this->_logger->info('Stopped propagation of event "{name}" at listener #{i} - "{listener}".', array(
                    'name' => $name,
                    'i' => $i,
                    'listener' => Debugger::callableToString($listener['callable'])
                ));
                break;
            }
        }

        return $event->isDefaultPrevented();
    }

    /**
     * Subscribe to an event.
     * 
     * @param string $name Name of the event to subscribe to.
     * @param callable $listener Listener. Anything that can be callable.
     * @param int $priority Priority of the execution. The higher, the sooner in the list it will be called. Default: 0.
     * 
     * @throws \InvalidArgumentException When $listener isn't callable.
     */
    public function subscribe($name, $listener, $priority = 0) {
        if (!is_callable($listener)) {
            throw new \InvalidArgumentException('Listener has to be a callable, "'. Debugger::getType($listener) .'" given."');
        }

        if (!isset($this->_listeners[$name])) {
            $this->_listeners[$name] = array();
        }

        $this->_listeners[$name][] = array(
            'callable' => $listener,
            'priority' => $priority
        );

        // already sort right after adding a listener
        $this->_listeners[$name] = ArrayUtils::multiSort($this->_listeners[$name], 'priority', true);
    }

    /**
     * Unsubscribes the given $listener from an event.
     * 
     * @param string $name Name of the event to unsubscribe from.
     * @param callable $listener Listener.
     */
    public function unsubscribe($name, $listener) {
        if (!isset($this->_listeners[$name])) {
            return;
        }

        $p = ArrayUtils::search($this->_listeners[$name], 'callable', $listener);
        if ($p !== false) {
            unset($this->_listeners[$name][$p]);
        }
    }

    /*
     * SETTERS AND GETTERS
     */
    /**
     * Returns all listeners.
     * 
     * @return array
     */
    public function getListeners() {
        return $this->_listeners;
    }

    /**
     * Returns all listeners for the given event.
     * 
     * @param string Name Name of the event.
     * @return array
     */
    public function getListenersForEvent($name) {
        if (!isset($this->_listeners[$name])) {
            return array();
        }

        return $this->_listeners[$name];
    }

}