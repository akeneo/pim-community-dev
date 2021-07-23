import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {CategoriesConfigurator} from './CategoriesConfigurator';
import {getDefaultTextSource} from '../Text/model';
import {getDefaultCategoriesSource} from './model';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

jest.mock('../common/CodeLabelCollectionSelector', () => ({
  CodeLabelCollectionSelector: ({
    onSelectionChange,
  }: {
    onSelectionChange: (updatedSelection: CodeLabelCollectionSelection) => void;
  }) => (
    <button
      onClick={() =>
        onSelectionChange({
          type: 'label',
          locale: 'en_US',
          separator: ',',
        })
      }
    >
      Update selection
    </button>
  ),
}));

test('it displays a categories configurator', () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CategoriesConfigurator
      source={{
        ...getDefaultCategoriesSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultCategoriesSource(),
    selection: {
      separator: ',',
      locale: 'en_US',
      type: 'label',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CategoriesConfigurator
      source={getDefaultTextSource(
        {
          code: 'text',
          type: 'pim_catalog_text',
          labels: {},
          scopable: false,
          localizable: false,
          is_locale_specific: false,
          available_locales: [],
        },
        null,
        null
      )}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "text" for categories configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
