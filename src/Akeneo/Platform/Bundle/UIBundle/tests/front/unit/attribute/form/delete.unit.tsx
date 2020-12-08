import ReactDOM from 'react-dom';
import {screen} from '@testing-library/react';
import '@testing-library/jest-dom';

let container: HTMLElement;
beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});
afterEach(() => {
  document.body.removeChild(container);
});

const getFormData = jest.fn().mockImplementation(() => ({
  code: 'foo',
}));

abstract class BaseViewMock {
  el: HTMLElement;

  constructor(container: HTMLElement) {
    this.el = container;
  }

  abstract reactElementToMount(): JSX.Element;

  render() {
    ReactDOM.render(this.reactElementToMount(), this.el);
  }

  getRoot() {
    return {
      getFormData: getFormData,
    };
  }
}

jest.mock('@akeneo-pim-community/legacy-bridge/src/bridge/react', () => ({ReactView: BaseViewMock}));

const Delete = require('pimui/js/attribute/form/delete');

test('it render a delete action button', () => {
  const component = new Delete(container);
  component.render();

  expect(screen.getByText('pim_common.delete')).toBeInTheDocument();
});
