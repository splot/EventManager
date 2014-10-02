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

abstract class AbstractEvent
{

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
