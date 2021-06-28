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
          type: 'amount',
        })
      }
    >
      Update selection
    </button>
  ),
}));

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
      type: 'amount',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  renderWithProviders(
    <MeasurementConfigurator
      source={getDefaultDateSource(dateAttribute, null, null)}
      attribute={dateAttribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "date_attribute" for measurement configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
