'use strict';

import React from 'react';
import * as ReactDOM from 'react-dom';
import '@testing-library/jest-dom/extend-expect';
import {act, fireEvent, getByLabelText, getByText} from '@testing-library/react';
import {AkeneoThemeProvider} from 'akeneomeasure/AkeneoThemeProvider';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';

const changeTextInputValue = (container: HTMLElement, label: string, value: string) => {
  const input = getByLabelText(container, label, {exact: false, trim: true});
  fireEvent.change(input, {target: {value: value}});
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
        <CreateMeasurementFamily
          onClose={() => {
          }}
        />
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
        <CreateMeasurementFamily
          onClose={mockOnClose}
        />
      </AkeneoThemeProvider>,
      container
    );
  });

  await act(async () => {
    const propertiesSection = getFormSectionByTitle(container, 'measurements.family.properties');
    changeTextInputValue(propertiesSection, 'measurements.form.input.code', 'custom_metric');
    changeTextInputValue(propertiesSection, 'measurements.form.input.label', 'My custom metric');
    const standardUnitSection = getFormSectionByTitle(container, 'measurements.family.standard_unit');
    changeTextInputValue(standardUnitSection, 'measurements.form.input.code', 'METER');
    changeTextInputValue(standardUnitSection, 'measurements.form.input.label', 'Meters');
    changeTextInputValue(standardUnitSection, 'measurements.form.input.symbol', 'm');

    const button = getByText(container, 'measurements.form.save');
    fireEvent.click(button);
  });

  expect(mockFetch).toHaveBeenCalled();
  expect(mockOnClose).toHaveBeenCalled();
});
