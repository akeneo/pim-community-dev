import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {CategoriesConfigurator} from './CategoriesConfigurator';
import {Attribute} from '../../../models/Attribute';
import {FetcherContext} from '../../../contexts';
import {getDefaultTextSource} from '../Text/model';
import {getDefaultCategoriesSource} from './model';
import {CodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

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

test('it displays a categories configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <CategoriesConfigurator
      source={{
        ...getDefaultCategoriesSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  const seletor = screen.getByText('Update selection');

  expect(seletor).toBeInTheDocument();
  userEvent.click(seletor);

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

test('it does not render if the source is not valid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  await renderWithProviders(
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
