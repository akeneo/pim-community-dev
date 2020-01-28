import * as React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByText} from '@testing-library/react';
import {renderHook} from '@testing-library/react-hooks';
import CompletenessFilter, {
  CompletenessValue,
} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';

const KeyEventSpace = {key: ' ', code: 32, charCode: 32, keyCode: 32};

const getDropdownButton = (container: HTMLElement): HTMLElement => {
  return container.querySelector(`.AknActionButton[tabindex="0"]`);
};

const getDropdownChoice = (container: HTMLElement, value: string) => {
  return container.querySelector(`.AknDropdown-menu [data-identifier="${value}"]`);
};

let container;
describe('Tests completeness filter', () => {
  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
    container = null;
  });

  it('It displays a completeness filter', async () => {
    const handleChange = jest.fn();

    await act(async () => {
      ReactDOM.render(<CompletenessFilter value={CompletenessValue.All} onChange={handleChange} />, container);
    });
  });

  it('I can change the completeness value', async () => {
    const handleChange = jest.fn();

    await act(async () => {
      ReactDOM.render(<CompletenessFilter value={CompletenessValue.All} onChange={handleChange} />, container);
    });

    const dropdownButton = getDropdownButton(container);
    fireEvent.click(dropdownButton);

    const dropdownChoice = getDropdownChoice(container, 'yes');
    fireEvent.click(dropdownChoice);

    expect(handleChange).toHaveBeenCalledWith(CompletenessValue.Yes);
  });

  it('I can change the completeness value with the keyboard', async () => {
    const handleChange = jest.fn();

    await act(async () => {
      ReactDOM.render(<CompletenessFilter value={CompletenessValue.All} onChange={handleChange} />, container);
    });

    const dropdownButton = getDropdownButton(container);
    fireEvent.keyPress(dropdownButton, KeyEventSpace);

    const dropdownChoice = getDropdownChoice(container, 'yes');
    fireEvent.keyPress(dropdownChoice, KeyEventSpace);

    expect(handleChange).toHaveBeenCalledWith(CompletenessValue.Yes);
  });
});
