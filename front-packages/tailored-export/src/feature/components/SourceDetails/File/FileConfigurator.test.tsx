import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {FileConfigurator} from './FileConfigurator';
import {FileSelection, getDefaultFileSource} from './model';
import {getDefaultTextSource} from '../Text/model';

const attribute = {
  code: 'file',
  type: 'pim_catalog_file',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

jest.mock('./FileSelector', () => ({
  FileSelector: ({onSelectionChange}: {onSelectionChange: (updatedSelection: FileSelection) => void}) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'name',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a file configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <FileConfigurator
      source={{
        ...getDefaultFileSource(attribute, null, null),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultFileSource(attribute, null, null),
    selection: {
      type: 'name',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it tells when the source data is invalid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const dateAttribute = {...attribute, type: 'pim_catalog_date', code: 'date_attribute'};

  expect(() => {
    renderWithProviders(
      <FileConfigurator
        source={getDefaultTextSource(dateAttribute, null, null)}
        attribute={dateAttribute}
        validationErrors={[]}
        onSourceChange={jest.fn()}
      />
    );
  }).toThrow('Invalid source data "date_attribute" for file configurator');

  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
