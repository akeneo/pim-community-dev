import {withEvents} from './events';

/**
 * Application wide event bus (historically `oro/mediator`).
 */
const mediator = withEvents({
  /**
   * Removes every event whose name, or whose string context, starts with the given namespace.
   */
  clear(namespace: string) {
    const self = mediator;
    const events = self._events || {};

    self._events = Object.keys(events)
      .filter(eventName => 0 !== eventName.indexOf(namespace))
      .reduce(
        (filteredEvents, eventName) => ({
          ...filteredEvents,
          [eventName]: events[eventName].filter(
            handler => typeof handler.context !== 'string' || 0 !== handler.context.indexOf(namespace)
          ),
        }),
        {}
      );
  },
});

export {mediator};
