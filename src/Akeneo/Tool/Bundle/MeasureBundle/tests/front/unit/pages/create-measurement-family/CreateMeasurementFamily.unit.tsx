import React from 'react';
import {act, screen} from '@testing-library/react';
import {CreateMeasurementFamily} from 'akeneomeasure/pages/create-measurement-family/CreateMeasurementFamily';
import {renderWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import userEvent from '@testing-library/user-event';
declare global {
  namespace NodeJS {
    interface Global {
      fetch: any;
    }
  }
}

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
    const codeInputs = screen.getAllByLabelText('pim_common.code pim_common.required_label');
    const labelInputs = screen.getAllByLabelText('pim_common.label');

    userEvent.type(codeInputs[0], 'custom_metric');
    userEvent.type(labelInputs[0], 'My custom metric');
    userEvent.type(codeInputs[1], 'METER');
    userEvent.type(labelInputs[1], 'Meters');
    userEvent.type(screen.getByLabelText('measurements.form.input.symbol'), 'm');

    userEvent.click(screen.getByText('pim_common.save'));
  });

  expect(mockFetch).toHaveBeenCalled();
  expect(mockOnClose).toHaveBeenCalled();
});
