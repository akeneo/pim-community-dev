import {Events, withEvents} from './events';

const createEmitter = () => withEvents({});

test('it triggers callbacks with arguments and context', () => {
  const emitter = createEmitter();
  const context = {name: 'context'};
  const callback = jest.fn(function (this: any) {
    expect(this).toBe(context);
  });

  emitter.on('change', callback, context);
  emitter.trigger('change', 'a', 'b');

  expect(callback).toHaveBeenCalledWith('a', 'b');
});

test('it uses the emitter as default context', () => {
  const emitter = createEmitter();
  let receivedContext = null;
  emitter.bind('change', function (this: any) {
    receivedContext = this;
  });
  emitter.trigger('change');

  expect(receivedContext).toBe(emitter);
});

test('it supports space separated event names and event maps', () => {
  const emitter = createEmitter();
  const first = jest.fn();
  const second = jest.fn();

  emitter.on('first second', first);
  emitter.on({second});
  emitter.trigger('first second', 'payload');

  expect(first).toHaveBeenCalledTimes(2);
  expect(second).toHaveBeenCalledWith('payload');
});

test('it notifies "all" listeners with the event name', () => {
  const emitter = createEmitter();
  const all = jest.fn();
  emitter.on('all', all);
  emitter.trigger('change', 42);

  expect(all).toHaveBeenCalledWith('change', 42);
});

test('it ignores registrations without callback and triggers without listeners', () => {
  const emitter = createEmitter();
  emitter.trigger('nothing');
  emitter.on('change');
  emitter.once('change');
  emitter.trigger('change');
  emitter.off('change');

  expect(emitter._events).toBeUndefined();
});

test('it triggers once callbacks only once', () => {
  const emitter = createEmitter();
  const callback = jest.fn();
  const context = {};
  emitter.once(
    'change',
    function (this: any, value: number) {
      expect(this).toBe(context);
      callback(value);
    },
    context
  );

  emitter.trigger('change', 1);
  emitter.trigger('change', 2);

  expect(callback).toHaveBeenCalledTimes(1);
  expect(callback).toHaveBeenCalledWith(1);
});

test('a once callback is only called once even when the event is re-triggered during dispatch', () => {
  const emitter = createEmitter();
  let nested = false;
  emitter.on('change', () => {
    if (!nested) {
      nested = true;
      emitter.trigger('change');
    }
  });
  const callback = jest.fn();
  emitter.once('change', callback);
  emitter.trigger('change');

  expect(callback).toHaveBeenCalledTimes(1);
});

test('it removes once callbacks by their original callback', () => {
  const emitter = createEmitter();
  const callback = jest.fn();
  emitter.once('change', callback);
  emitter.off('change', callback);
  emitter.trigger('change');

  expect(callback).not.toHaveBeenCalled();
});

test('it removes callbacks by name, callback or context', () => {
  const emitter = createEmitter();
  const contextA = {};
  const contextB = {};
  const a = jest.fn();
  const b = jest.fn();
  const c = jest.fn();

  emitter.on('a', a, contextA);
  emitter.on('a', b, contextB);
  emitter.on('c', c, contextA);

  emitter.off(null, null, contextA);
  emitter.trigger('a');
  emitter.trigger('c');
  expect(a).not.toHaveBeenCalled();
  expect(c).not.toHaveBeenCalled();
  expect(b).toHaveBeenCalledTimes(1);

  emitter.unbind('a', b);
  emitter.off('unknown', b);
  emitter.trigger('a');
  expect(b).toHaveBeenCalledTimes(1);

  emitter.on('a', a);
  emitter.off('a');
  emitter.trigger('a');
  expect(a).not.toHaveBeenCalled();
});

test('it removes every callback', () => {
  const emitter = createEmitter();
  const callback = jest.fn();
  emitter.on('a', callback);
  emitter.off();
  emitter.trigger('a');

  expect(callback).not.toHaveBeenCalled();
});

test('it does nothing when removing callbacks from an emitter without events', () => {
  const emitter = createEmitter();

  expect(emitter.off('a')).toBe(emitter);
});

test('it listens to other emitters and stops listening', () => {
  const listener = createEmitter();
  const emitter = createEmitter();
  const otherEmitter = createEmitter();
  const callback = jest.fn(function (this: any) {
    expect(this).toBe(listener);
  });

  listener.listenTo(emitter, 'change', callback);
  listener.listenTo(emitter, {mapped: callback});
  listener.listenTo(otherEmitter, 'change', callback);
  emitter.trigger('change');
  emitter.trigger('mapped');
  expect(callback).toHaveBeenCalledTimes(2);

  listener.stopListening(emitter, 'change', callback);
  emitter.trigger('change');
  expect(callback).toHaveBeenCalledTimes(2);

  listener.stopListening(emitter);
  emitter.trigger('mapped');
  expect(callback).toHaveBeenCalledTimes(2);
  expect(listener._listeners).not.toHaveProperty(emitter._listenerId as string);

  listener.stopListening();
  otherEmitter.trigger('change');
  expect(callback).toHaveBeenCalledTimes(2);
  expect(listener._listeners).toEqual({});
});

test('it stops listening to event maps', () => {
  const listener = createEmitter();
  const emitter = createEmitter();
  const callback = jest.fn();
  listener.listenTo(emitter, {change: callback});
  listener.stopListening(undefined, {change: callback});
  emitter.trigger('change');

  expect(callback).not.toHaveBeenCalled();
});

test('it does nothing when stopping to listen without listeners', () => {
  const listener = createEmitter();

  expect(listener.stopListening()).toBe(listener);
});

test('it exposes the raw mixin', () => {
  expect(typeof Events.on).toBe('function');
});
