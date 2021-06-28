import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {FamilyVariantConfigurator} from './FamilyVariantConfigurator';
import {Attribute} from '../../../models/Attribute';
import {FetcherContext} from '../../../contexts';
import {getDefaultTextSource} from '../Text/model';
import {CodeLabelSelection} from '../common/CodeLabelSelector';

const attribute = {
  code: 'text',
  type: 'pim_catalog_text',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

jest.mock('../common/CodeLabelSelector', () => ({
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

test('it displays a family variant configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <FamilyVariantConfigurator
      source={{
        channel: null,
        code: 'family_variant',
        locale: null,
        operations: {},
        selection: {
          type: 'code',
        },
        type: 'property',
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  userEvent.click(screen.getByText('Update selection'));

  expect(onSourceChange).toHaveBeenCalledWith({
    channel: null,
    code: 'family_variant',
    locale: null,
    operations: {},
    selection: {
      locale: 'en_US',
      type: 'label',
    },
    type: 'property',
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <FamilyVariantConfigurator
      source={getDefaultTextSource(attribute, null, null)}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "text" for family variant configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
