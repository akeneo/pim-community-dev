import React from 'react';
import $ from 'jquery';
import {act} from 'react-dom/test-utils';
import {mediator} from '@akeneo-pim-community/shared';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react/reactController';

class HelloController extends ReactController {
  reactElementToMount() {
    return <div>Hello</div>;
  }

  routeGuardToUnmount() {
    return /^hello_/;
  }
}

test('it renders the React element in its element and returns a resolved jQuery promise', () => {
  const el = document.createElement('div');
  const controller = new HelloController({el: $(el)});

  let promise: any;
  act(() => {
    promise = controller.renderRoute();
  });

  expect(controller.el).toBe(el);
  expect(el.textContent).toBe('Hello');
  expect(promise.state()).toBe('resolved');
});

test('it creates its element when none is given', () => {
  expect(new HelloController().el).toBeInstanceOf(HTMLElement);
  expect(new HelloController({el: document.createElement('span')}).el.tagName).toBe('SPAN');
});

test('it keeps the React element mounted on routes matching its guard', () => {
  const controller = new HelloController();
  act(() => {
    controller.renderRoute();
  });

  mediator.trigger('route_start', 'hello_world');
  expect(controller.el.textContent).toBe('Hello');

  act(() => {
    mediator.trigger('route_start', 'another_route');
  });
  expect(controller.el.textContent).toBe('');
  controller.remove();
});

test('it appends a static container to its element', () => {
  const container = document.createElement('section');
  class StaticContainerController extends HelloController {
    getContainerRef() {
      return container;
    }
  }
  const controller = new StaticContainerController();
  act(() => {
    controller.renderRoute();
  });

  expect(controller.el.firstChild).toBe(container);
});

test('it asks its listeners if it can be left', () => {
  const controller = new HelloController();
  expect(controller.canLeave()).toBe(true);

  controller.on('pim:controller:can-leave', (event: {canLeave: boolean}) => (event.canLeave = false));
  expect(controller.canLeave()).toBe(false);
});

test('it is removed from the DOM and stops listening to route changes', () => {
  const parent = document.createElement('div');
  const el = document.createElement('div');
  parent.appendChild(el);
  const controller = new HelloController({el});
  controller.setActive(true);
  const off = jest.spyOn(mediator, 'off');

  controller.remove();

  expect(controller.active).toBe(false);
  expect(parent.children).toHaveLength(0);
  expect(off).toHaveBeenCalledWith('route_start', expect.any(Function), controller);
});
