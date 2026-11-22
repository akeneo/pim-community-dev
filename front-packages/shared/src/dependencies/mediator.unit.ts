import {mediator} from './mediator';

afterEach(() => mediator.off());

test('it is an event bus', () => {
  const callback = jest.fn();
  mediator.on('pim:event', callback);
  mediator.trigger('pim:event', 'payload');

  expect(callback).toHaveBeenCalledWith('payload');
});

test('it clears events by namespace, by name and by string context', () => {
  const namespaced = jest.fn();
  const kept = jest.fn();
  const namespacedContext = jest.fn();

  mediator.on('grid:load', namespaced);
  mediator.on('route_start', kept, {});
  mediator.on('route_start', namespacedContext, 'grid:context');
  mediator.clear('grid');

  mediator.trigger('grid:load');
  mediator.trigger('route_start');

  expect(namespaced).not.toHaveBeenCalled();
  expect(namespacedContext).not.toHaveBeenCalled();
  expect(kept).toHaveBeenCalled();
});

test('it clears without any registered event', () => {
  delete mediator._events;
  mediator.clear('grid');

  expect(mediator._events).toEqual({});
});
