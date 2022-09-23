import React from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '../../../tests';
import {MeasurementRounding} from './MeasurementRounding';

const flushPromises = () => new Promise(setImmediate);

test('it can update the rounding type', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementRounding operation={undefined} validationErrors={[]} onOperationChange={onOperationChange} />
  );
  await act(async () => {
    await flushPromises();
  });

  userEvent.click(
    screen.getByText(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.label'
    )
  );
  userEvent.click(
    screen.getByText(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.types.round_up'
    )
  );

  expect(onOperationChange).toHaveBeenCalledWith({
    type: 'measurement_rounding',
    rounding_type: 'round_up',
    precision: 2,
  });
});

test('it can update the rounding precision', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementRounding
      operation={{type: 'measurement_rounding', rounding_type: 'standard', precision: 2}}
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );
  await act(async () => {
    await flushPromises();
  });

  userEvent.type(
    screen.getByPlaceholderText(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.precision.placeholder'
    ),
    '6'
  );

  expect(onOperationChange).toHaveBeenCalledWith({
    type: 'measurement_rounding',
    rounding_type: 'standard',
    precision: 26,
  });
});

test('it can reset the rounding', async () => {
  const onOperationChange = jest.fn();

  await renderWithProviders(
    <MeasurementRounding
      operation={{type: 'measurement_rounding', rounding_type: 'standard', precision: 2}}
      validationErrors={[]}
      onOperationChange={onOperationChange}
    />
  );
  await act(async () => {
    await flushPromises();
  });

  userEvent.click(
    screen.getByText(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.label'
    )
  );
  userEvent.click(
    screen.getByTitle(
      'akeneo.syndication.data_mapping_details.sources.operation.measurement_rounding.rounding_type.types.no_rounding'
    )
  );

  expect(onOperationChange).toHaveBeenCalledWith(undefined);
});

test('it can display validation errors', async () => {
  const onOperationChange = jest.fn();
  const errorMessage = 'error.key.precision';

  await renderWithProviders(
    <MeasurementRounding
      operation={{type: 'measurement_rounding', rounding_type: 'standard', precision: 2}}
      validationErrors={[
        {
          messageTemplate: errorMessage,
          parameters: {},
          message: 'string',
          propertyPath: '[precision]',
          invalidValue: null,
        },
      ]}
      onOperationChange={onOperationChange}
    />
  );

  expect(screen.getByRole('alert')).toBeInTheDocument();
  expect(screen.getByText(errorMessage)).toBeInTheDocument();
});
