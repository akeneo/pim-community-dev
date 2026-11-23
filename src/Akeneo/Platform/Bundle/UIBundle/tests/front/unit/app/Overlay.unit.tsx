import React from 'react';
import ReactDOM from 'react-dom';
import {act} from 'react-dom/test-utils';
import {mediator} from '@akeneo-pim-community/shared';
import {Overlay} from '../../../../Resources/public/js/app/Overlay';

let root: HTMLElement;
beforeEach(() => {
  root = document.createElement('div');
  document.body.appendChild(root);
  act(() => {
    ReactDOM.render(<Overlay />, root);
  });
});
afterEach(() => {
  ReactDOM.unmountComponentAtNode(root);
  root.remove();
});

const overlay = () => root.querySelector('#overlay') as HTMLElement;

test('it is shown and hidden by the side panels through the mediator', () => {
  expect(overlay().className).toBe('AknOverlay');

  act(() => {
    mediator.trigger('pim-app:overlay:show');
  });
  expect(overlay().className).toBe('AknOverlay AknOverlay--show');

  act(() => {
    mediator.trigger('pim-app:overlay:hide');
  });
  expect(overlay().className).toBe('AknOverlay');
});

test('a click on it asks the side panels to close', () => {
  const close = jest.fn();
  mediator.on('pim-app:panel:close', close);

  act(() => {
    overlay().click();
  });

  expect(close).toHaveBeenCalledTimes(1);
  mediator.off('pim-app:panel:close', close);
});

test('it stops listening to the mediator once removed', () => {
  ReactDOM.unmountComponentAtNode(root);

  expect(() => mediator.trigger('pim-app:overlay:show')).not.toThrow();
  expect(root.querySelector('#overlay')).toBeNull();
});
