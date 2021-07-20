import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {FileConfigurator} from './FileConfigurator';
import {Attribute} from '../../../models/Attribute';
import {FetcherContext} from '../../../contexts';
import {FileSelection, getDefaultFileSource} from './model';
import {getDefaultTextSource} from '../Text/model';
import {AssociationType} from '../../../models';

const attribute = {
  code: 'file',
  type: 'pim_catalog_file',
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
};

const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>([])},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
  associationType: {fetchByCodes: (): Promise<AssociationType[]> => Promise.resolve([])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node}</FetcherContext.Provider>));

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

test('it displays a file configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
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

test('it does not render if the source is not valid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <FileConfigurator
      source={getDefaultTextSource(attribute, null, null)}
      attribute={attribute}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "file" for file configurator');
  expect(screen.queryByText('Update selection')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
