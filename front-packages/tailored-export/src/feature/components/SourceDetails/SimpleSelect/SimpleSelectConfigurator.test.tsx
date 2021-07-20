import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {SimpleSelectConfigurator} from './SimpleSelectConfigurator';
import {getDefaultSimpleSelectSource} from './model';
import {getDefaultDateSource} from '../Date/model';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

const attribute = {
  code: 'simpleselect',
  type: 'pim_catalog_simpleselect',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('../common/CodeLabelSelector', () => ({
  ...jest.requireActual('../common/CodeLabelSelector'),
  CodeLabelSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: CodeLabelSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'label',
          locale: 'en_US',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a simple select configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <SimpleSelectConfigurator
      source={{
        ...getDefaultSimpleSelectSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultSimpleSelectSource(attribute, null, null),
    selection: {
      type: 'label',
      locale: 'en_US',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  renderWithProviders(
    <SimpleSelectConfigurator
      source={getDefaultDateSource(dateAttribute, null, null)}
      attribute={dateAttribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "date_attribute" for simple select configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
