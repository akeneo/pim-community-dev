import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {NumberConfigurator} from './NumberConfigurator';
import {NumberSelection, getDefaultNumberSource} from './model';
import {getDefaultDateSource} from '../Date/model';

const attribute = {
  code: 'number',
  type: 'pim_catalog_number',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('./NumberSelector', () => ({
  NumberSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: NumberSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          decimal_separator: '.',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a number configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <NumberConfigurator
      source={{
        ...getDefaultNumberSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultNumberSource(attribute, null, null),
    selection: {
      decimal_separator: '.',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  renderWithProviders(
    <NumberConfigurator
      source={getDefaultDateSource(dateAttribute, null, null)}
      attribute={dateAttribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "date_attribute" for number configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
