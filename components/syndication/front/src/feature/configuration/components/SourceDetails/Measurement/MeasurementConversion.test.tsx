import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {MeasurementConversion} from './MeasurementConversion';
import {renderWithProviders} from '../../../tests';

const flushPromises = () => new Promise(setImmediate);

test('it can update a conversion unit', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementConversion
      operation={undefined}
      measurementFamilyCode="Weight"
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );
  await act(async () => {
    await flushPromises();
  });

  userEvent.click(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.title')
  );
  userEvent.click(
    screen.getByLabelText(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.target_unit_code.label'
    )
  );
  userEvent.click(screen.getByText('Meter'));

  expect(onOperationChange).toHaveBeenCalledWith({type: 'measurement_conversion', target_unit_code: 'meter'});
});

test('it can reset the conversion', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementConversion
      operation={{type: 'measurement_conversion', target_unit_code: 'meter'}}
      measurementFamilyCode="Weight"
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );
  await act(async () => {
    await flushPromises();
  });

  userEvent.click(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.operation.measurement_conversion.title')
  );
  userEvent.click(screen.getByTitle('pim_common.clear_value'));

  expect(onOperationChange).toHaveBeenCalledWith(undefined);
});

test('it can display validation errors', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementConversion
      operation={{type: 'measurement_conversion', target_unit_code: 'meter'}}
      measurementFamilyCode="Weight"
      validationErrors={[
        {
          messageTemplate: 'error.key.target_unit_code',
          parameters: {},
          message: 'string',
          propertyPath: '[target_unit_code]',
          invalidValue: null,
        },
      ]}
      onOperationChange={onOperationChange}
    />
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
  expect(screen.getByText('error.key.target_unit_code')).toBeInTheDocument();
});
