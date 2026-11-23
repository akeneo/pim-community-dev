/**
 * Dependency-free event emitter, API compatible with Backbone.Events (0.9.10).
 *
 * It lets legacy Backbone views keep calling `this.listenTo(emitter, ...)` / `this.stopListening()` on objects that
 * no longer depend on Backbone, while giving React code a plain on/off/trigger API.
 * The internal storage (`_events`) keeps the Backbone layout so legacy helpers relying on it keep working.
 */

type EventCallback = ((...args: any[]) => void) & {_callback?: (...args: any[]) => void};
type EventMap = {[eventName: string]: EventCallback};
type EventHandler = {callback: EventCallback; context: any; ctx: any};

type Events = {
  _events?: {[eventName: string]: EventHandler[]};
  _listeners?: {[listenerId: string]: Events};
  _listenerId?: string;
  on(name: string | EventMap, callback?: EventCallback | null, context?: any): any;
  bind(name: string | EventMap, callback?: EventCallback | null, context?: any): any;
  once(name: string | EventMap, callback?: EventCallback | null, context?: any): any;
  off(name?: string | EventMap | null, callback?: EventCallback | null, context?: any): any;
  unbind(name?: string | EventMap | null, callback?: EventCallback | null, context?: any): any;
  trigger(name: string, ...args: any[]): any;
  listenTo(other: Events, name: string | EventMap, callback?: EventCallback): any;
  stopListening(other?: Events, name?: string | EventMap, callback?: EventCallback): any;
};

const eventSplitter = /\s+/;
let listenerIdCounter = 0;

/**
 * Handles the "event map" and "space separated event names" syntaxes.
 * Returns false when the call has been dispatched (so the caller must stop), true otherwise.
 */
const eventsApi = (target: Events, action: 'on' | 'once' | 'off' | 'trigger', name: any, rest: any[]): boolean => {
  if (!name) {
    return true;
  }

  if (typeof name === 'object') {
    Object.keys(name).forEach(key => (target[action] as any)(key, name[key], ...rest));

    return false;
  }

  if (eventSplitter.test(name)) {
    name.split(eventSplitter).forEach((eventName: string) => (target[action] as any)(eventName, ...rest));

    return false;
  }

  return true;
};

const triggerEvents = (handlers: EventHandler[], args: any[]) => {
  const length = handlers.length;
  for (let i = 0; i < length; i++) {
    handlers[i].callback.apply(handlers[i].ctx, args);
  }
};

const Events: Events = {
  on(this: Events, name, callback, context) {
    if (!(eventsApi(this, 'on', name, [callback, context]) && callback)) {
      return this;
    }
    this._events = this._events || {};
    const handlers = this._events[name as string] || (this._events[name as string] = []);
    handlers.push({callback, context, ctx: context || this});

    return this;
  },

  bind(this: Events, name, callback, context) {
    return this.on(name, callback, context);
  },

  once(this: Events, name, callback, context) {
    if (!(eventsApi(this, 'once', name, [callback, context]) && callback)) {
      return this;
    }
    const self = this;
    let called = false;
    const once: EventCallback = function (this: any, ...args: any[]) {
      if (called) {
        return;
      }
      called = true;
      self.off(name, once);
      callback.apply(this, args);
    };
    once._callback = callback;

    return this.on(name, once, context);
  },

  off(this: Events, name, callback, context) {
    if (!this._events || !eventsApi(this, 'off', name, [callback, context])) {
      return this;
    }
    if (!name && !callback && !context) {
      this._events = {};

      return this;
    }

    const names = name ? [name as string] : Object.keys(this._events);
    names.forEach(eventName => {
      const handlers = this._events![eventName];
      if (!handlers) {
        return;
      }
      this._events![eventName] =
        callback || context
          ? handlers.filter(
              handler =>
                (callback && callback !== handler.callback && callback !== handler.callback._callback) ||
                (context && context !== handler.context)
            )
          : [];
    });

    return this;
  },

  unbind(this: Events, name, callback, context) {
    return this.off(name, callback, context);
  },

  trigger(this: Events, name, ...args) {
    if (!this._events || !eventsApi(this, 'trigger', name, args)) {
      return this;
    }
    const handlers = this._events[name];
    const allHandlers = this._events.all;
    if (handlers) {
      triggerEvents(handlers, args);
    }
    if (allHandlers) {
      triggerEvents(allHandlers, [name, ...args]);
    }

    return this;
  },

  listenTo(this: Events, other, name, callback) {
    const listeners = this._listeners || (this._listeners = {});
    const id = other._listenerId || (other._listenerId = `l${++listenerIdCounter}`);
    listeners[id] = other;
    if (typeof name === 'object') {
      Object.keys(name).forEach(key => other.on(key, name[key], this));
    } else {
      other.on(name, callback, this);
    }

    return this;
  },

  stopListening(this: Events, other, name, callback) {
    const listeners = this._listeners;
    if (!listeners) {
      return this;
    }
    if (other) {
      other.off(name, callback, this);
      if (!name && !callback) {
        delete listeners[other._listenerId as string];
      }
    } else {
      Object.keys(listeners).forEach(id => listeners[id].off(name, callback, this));
      this._listeners = {};
    }

    return this;
  },
};

/**
 * Returns a new object made of the given properties and the event methods.
 */
const withEvents = <T extends object>(target: T): T & Events => Object.assign(target, Events);

export {Events, withEvents};
export type {EventCallback, EventMap};
