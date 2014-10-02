Splot Event Manager
============

Simple event manager for PHP.

[![Build Status](https://travis-ci.org/splot/EventManager.svg?branch=master)](https://travis-ci.org/splot/EventManager)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5e194e18-8c55-483b-a376-243943eb76b8/mini.png)](https://insight.sensiolabs.com/projects/5e194e18-8c55-483b-a376-243943eb76b8)
[![HHVM Status](http://hhvm.h4cc.de/badge/splot/EventManager.png)](http://hhvm.h4cc.de/package/splot/EventManager)

## Features

- Stop further propagation of events / stop other listeners from being called.
- Mark in the event that default action should be prevented.
- All events are classes and objects.

## Installation

You can install Splot Event Manager using [Composer](https://getcomposer.org/).

    $ composer require splot/event-manager dev-master

All you need to do to start using it is instantiation:

    $eventManager = new \Splot\EventManager\EventManager();

You can optionally pass a `Psr\Log\LoggerInterface` to the constructor for debug messages.

## Events

In Splot Event Manager every event is an instance of a class that extends `Splot\EventManager\AbstractEvent`. It means that every event name is set in that event class (by default and convention an event name is its full namespaced class name) and actual event objects are passed to all listeners. They may or may not contain any additional data and it's fully responsibility of the code that triggers an event to populate it with data.

If you want to use an event name (e.g. to register a listener) it is best practice to get it from the event class static method `::getName()`.

By convention, all events should be read-only, so it is best to pass any data to them in their constructors and not allow for it to be altered at later time (e.g. by storing them in `protected` or `private` attributes and not creating setters for them).

## Triggering events

To trigger an event you need to just call `EventManager::trigger()` method with the event instance.

    $eventManager->trigger(new MyEvent());

This method returns `boolean` for convenience checking if a default action that follows the event should be executed or not. See below for details.

## Listening for events

To listen for an event you can subscribe a `callable` (most usually a closure). The listener is then called with a single argument - the event instance.

    $eventManager->subscribe(MyEvent::getName(), function($event) {
        // do something
    });

#### Listener priority

Listeners are called in the order they were registered, but you can subscribe listeners with a priority (higher priority means that they will be executed first). By default, the priority is `0`, but it can be both positive and negative.

Simply pass the listener priority as 3rd (last) argument of the `::subscribe()` method.

    $eventManager->subscribe(MyEvent::getName(), function($event) {
        // listener one
    }, -20);

    $eventManager->subscribe(MyEvent::getName(), function($event) {
        // listener two
    }, 30);

In the example above, the second listener will be executed first when `MyEvent` is triggered.

## Other

#### Stopping propagation

When listening for an event you can also prevent it from calling other subsequent listeners.

    $event->stopPropagation()

Calling this method will let the Event Manager know that no further listeners should be notified about the `$event`.

#### Preventing default action

The event can let the triggering code know that default action that follows it should not be executed. This is simply done by calling

    $event->preventDefault()

or by returning `false` from a listener.

The triggering code can then check on the event instance:

    $eventManager->trigger($event);
    if (!$event->isDefaultPrevented()) {
        // do something that is suppose to happen
    }

For convenience, the `EventManager::trigger()` method also returns information whether or not the default action should be prevented (it returns `true` if default has not been prevented). The above example can be written like this:

    if ($eventManager->trigger($event)) {
        // do something that is suppose to happen
    }

## Contribute

Issues and pull requests are very welcome! When creating a pull request please include full test coverage to your changes.
