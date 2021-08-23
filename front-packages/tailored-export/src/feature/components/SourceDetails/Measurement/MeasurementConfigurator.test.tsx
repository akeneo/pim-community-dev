import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {MeasurementConfigurator} from './MeasurementConfigurator';
import {MeasurementSelection, getDefaultMeasurementSource} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'measurement',
  type: 'pim_catalog_metric',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
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

jest.mock('../common/DefaultValue');

test('it displays a measurement configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <MeasurementConfigurator
      source={{
        ...getDefaultMeasurementSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, null, null),
    selection: {
      type: 'value',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it can update default value operation', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <MeasurementConfigurator
      source={{
        ...getDefaultMeasurementSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Default value'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultMeasurementSource(attribute, null, null),
    operations: {
      default_value: {
        type: 'default_value',
        value: 'foo',
      },
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <MeasurementConfigurator
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
