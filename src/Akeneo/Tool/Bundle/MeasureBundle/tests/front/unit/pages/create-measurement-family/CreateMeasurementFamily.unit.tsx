'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText} from '@testing-library/react';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';

declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

const changeTextInputValue = async (container: HTMLElement, label: string, value: string) => {
  const input = getByLabelText(container, label, {exact: false, trim: true});
  await fireEvent.change(input, {target: {value: value}});
};

const getFormSectionByTitle = (container: HTMLElement, title: string): HTMLElement => {
  const header = getByText(container, title);
  return header.parentElement as HTMLElement;
};

let container: HTMLElement;

beforeEach(() => {
  container = document.createElement('div');
  document.body.appendChild(container);
});

afterEach(() => {
  document.body.removeChild(container);
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It renders without errors', async () => {
  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <CreateMeasurementFamily onClose={() => {}} />
      </AkeneoThemeProvider>,
      container
    );
  });
});

test('I can fill the fields and save', async () => {
  const mockFetch = jest.fn().mockImplementation(() => ({
    ok: true,
  }));
  const mockOnClose = jest.fn();

  global.fetch = mockFetch;

  await act(async () => {
    ReactDOM.render(
      <AkeneoThemeProvider>
        <CreateMeasurementFamily onClose={mockOnClose} />
      </AkeneoThemeProvider>,
      container
    );
  });

  await act(async () => {
    const propertiesSection = getFormSectionByTitle(container, 'pim_common.properties');
    await changeTextInputValue(propertiesSection, 'pim_common.code', 'custom_metric');
    await changeTextInputValue(propertiesSection, 'pim_common.label', 'My custom metric');
    const standardUnitSection = getFormSectionByTitle(container, 'measurements.family.standard_unit');
    await changeTextInputValue(standardUnitSection, 'pim_common.code', 'METER');
    await changeTextInputValue(standardUnitSection, 'pim_common.label', 'Meters');
    await changeTextInputValue(standardUnitSection, 'measurements.form.input.symbol', 'm');

    const button = getByText(container, 'pim_common.save');
    await fireEvent.click(button);
  });

  expect(mockFetch).toHaveBeenCalled();
  expect(mockOnClose).toHaveBeenCalled();
});
