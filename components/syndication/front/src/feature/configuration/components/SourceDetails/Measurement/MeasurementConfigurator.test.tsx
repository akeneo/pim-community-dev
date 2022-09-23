import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MeasurementConfigurator} from './MeasurementConfigurator';
import {MeasurementSelection, getDefaultMeasurementSource, MeasurementRoundingOperation} from './model';
import {getDefaultDateSource} from '../Date/model';
import {MeasurementConversionOperation} from './MeasurementConversion';

const attribute = {
  code: 'measurement',
  type: 'pim_catalog_metric',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
  metric_family: 'Length',
};

jest.mock('./MeasurementSelector', () => ({
  MeasurementSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: MeasurementSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'value',
        })
      }
    >
      Update selection
    </button>
  ),
}));

jest.mock('./MeasurementConversion', () => ({
  MeasurementConversion: ({
    onOperationChange,
  }: {
    onOperationChange: (updatedOperation: MeasurementConversionOperation) => void;
  }) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'measurement_conversion',
          target_unit_code: 'meter',
        })
      }
    >
      Measurement conversion
    </button>
  ),
}));

jest.mock('./MeasurementRounding', () => ({
  MeasurementRounding: ({
    onOperationChange,
  }: {
    onOperationChange: (updatedOperation: MeasurementRoundingOperation) => void;
  }) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'measurement_rounding',
          rounding_type: 'no_rounding',
        })
      }
    >
      Measurement rounding
    </button>
  ),
}));

jest.mock('../common/DefaultValue');

test('it displays a measurement configurator', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'measurement',
    label: 'Measurement',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  const target = {
    type: 'string' as const,
    name: 'measurement',
    required: false,
  };

  renderWithProviders(
    <MeasurementConfigurator
      requirement={requirement}
      source={{
        ...getDefaultMeasurementSource(attribute, target, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, target, null, null),
    selection: {
      type: 'value',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'measurement',
    label: 'Measurement',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  const target = {
    type: 'string' as const,
    name: 'measurement',
    required: false,
  };

  renderWithProviders(
    <MeasurementConfigurator
      requirement={requirement}
      source={{
        ...getDefaultMeasurementSource(attribute, target, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, target, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update measurement conversion operation', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'measurement',
    label: 'Measurement',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  const target = {
    type: 'string' as const,
    name: 'measurement',
    required: false,
  };

  renderWithProviders(
    <MeasurementConfigurator
      requirement={requirement}
      source={{
        ...getDefaultMeasurementSource(attribute, target, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Measurement conversion'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, target, null, null),
    operations: {
      measurement_conversion: {
        type: 'measurement_conversion',
        target_unit_code: 'meter',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update measurement rounding operation', () => {
  const onSourceChange = jest.fn();
  const requirement = {
    code: 'measurement',
    label: 'Measurement',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  const target = {
    type: 'string' as const,
    name: 'measurement',
    required: false,
  };

  renderWithProviders(
    <MeasurementConfigurator
      requirement={requirement}
      source={{
        ...getDefaultMeasurementSource(attribute, target, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Measurement rounding'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, target, null, null),
    operations: {
      measurement_rounding: {
        type: 'measurement_rounding',
        rounding_type: 'no_rounding',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};
  const requirement = {
    code: 'measurement',
    label: 'Measurement',
    help: '',
    group: '',
    examples: [],
    type: 'string' as const,
    required: false,
  };

  expect(() => {
    renderWithProviders(
      <MeasurementConfigurator
        requirement={requirement}
        source={getDefaultDateSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for measurement configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
