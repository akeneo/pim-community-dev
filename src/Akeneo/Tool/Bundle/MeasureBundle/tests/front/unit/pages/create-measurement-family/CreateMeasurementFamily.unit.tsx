'use strict';

import React from 'react';
import {act, fireEvent, getByLabelText, screen} from '@testing-library/react';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';

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

const getFormSectionByTitle = (title: string): HTMLElement => {
  const header = screen.getByText(title);
  return header.parentElement as HTMLElement;
};

afterAll(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It renders without errors', async () => {
  renderWithProviders(<CreateMeasurementFamily isOpen={true} onClose={() => {}} />);
});

test('I can fill the fields and save', async () => {
  const mockFetch = jest.fn().mockImplementation(() => ({
    ok: true,
  }));
  const mockOnClose = jest.fn();

  global.fetch = mockFetch;

  renderWithProviders(<CreateMeasurementFamily isOpen={true} onClose={mockOnClose} />);

  await act(async () => {
    const propertiesSection = getFormSectionByTitle('pim_common.properties');
    await changeTextInputValue(propertiesSection, 'pim_common.code', 'custom_metric');
    await changeTextInputValue(propertiesSection, 'pim_common.label', 'My custom metric');
    const standardUnitSection = getFormSectionByTitle('measurements.family.standard_unit');
    await changeTextInputValue(standardUnitSection, 'pim_common.code', 'METER');
    await changeTextInputValue(standardUnitSection, 'pim_common.label', 'Meters');
    await changeTextInputValue(standardUnitSection, 'measurements.form.input.symbol', 'm');

    fireEvent.click(screen.getByText('pim_common.save'));
  });

  expect(mockFetch).toHaveBeenCalled();
  expect(mockOnClose).toHaveBeenCalled();
});
