<?php
/**
 * Abstract event class.
 * 
 * @package SplotEventManager
 * @author Michał Dudek <michal@michaldudek.pl>
 * 
 * @copyright Copyright (c) 2013, Michał Dudek
 * @license MIT
 */
namespace Splot\EventManager;

use Splot\EventManager\EventManager;

abstract class AbstractEvent
{

    /**
     * Event manager.
     * 
     * @var EventManager
     */
    private $_eventManager;

    /**
     * Has any further propagation of this event been stopped?
     * 
     * @var bool
     */
    private $_propagationStopped = false;

    /**
     * Has default behavior been prevented?
     * 
     * @var bool
     */
    private $_defaultPrevented = false;

    /**
     * Sets the event manager.
     * 
     * @param EventManager $eventManager
     */
    public function setEventManager(EventManager $eventManager) {
        $this->_eventManager = $eventManager;
    }

    /**
     * Gets the event manager.
     * 
     * @return EventManager
     */
    public function getEventManager() {
        return $this->_eventManager;
    }

    /**
     * Stops the further propagation of this event.
     */
    public function stopPropagation() {
        $this->_propagationStopped = true;
    }

    /**
     * Prevents the default behavior of this event.
     */
    public function preventDefault() {
        $this->_defaultPrevented = true;
    }

    /**
     * Checks if propagation of this event has been stopped.
     * 
     * @return bool
     */
    public function isPropagationStopped() {
        return $this->_propagationStopped;
    }

    /**
     * Checks if the default behavior of this event has been prevented.
     * 
     * @return bool
     */
    public function isDefaultPrevented() {
        return $this->_defaultPrevented;
    }

    /**
     * Returns the name of this event.
     * 
     * @return string
     */
    public static function getName() {
        return get_called_class();
    }

}