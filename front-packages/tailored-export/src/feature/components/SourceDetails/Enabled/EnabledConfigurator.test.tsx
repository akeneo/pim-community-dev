import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender} from '@akeneo-pim-community/shared';
import {EnabledConfigurator} from './EnabledConfigurator';
import {Attribute} from '../../../models/Attribute';
import {FetcherContext} from '../../../contexts';
import {getDefaultEnabledSource} from './model';
import {getDefaultTextSource} from '../Text/model';
import {BooleanReplacementOperation} from '../common/BooleanReplacement';

const attribute = {
  code: 'date',
  type: 'pim_catalog_date',
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

jest.mock('../common/BooleanReplacement', () => ({
  BooleanReplacement: ({
    onOperationChange,
  }: {
    onOperationChange: (updatedOperation: BooleanReplacementOperation) => void;
  }) => (
    <button
      onClick={() =>
        onOperationChange({
          type: 'replacement',
          mapping: {
            true: 'activé',
            false: 'désactivé',
          },
        })
      }
    >
      Update replacement
    </button>
  ),
}));

test('it displays an enabled configurator', async () => {
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <EnabledConfigurator
      source={{
        ...getDefaultEnabledSource(),
        uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
      }}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  const replacement = screen.getByText('Update replacement');

  expect(replacement).toBeInTheDocument();
  userEvent.click(replacement);

  expect(onSourceChange).toHaveBeenCalledWith({
    ...getDefaultEnabledSource(),
    operations: {
      replacement: {
        type: 'replacement',
        mapping: {
          true: 'activé',
          false: 'désactivé',
        },
      },
    },
    selection: {
      type: 'code',
    },
    uuid: 'e612bc67-9c30-4121-8b8d-e08b8c4a0640',
  });
});

test('it does not render if the source is not valid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const onSourceChange = jest.fn();

  await renderWithProviders(
    <EnabledConfigurator
      source={getDefaultTextSource(attribute, null, null)}
      validationErrors={[]}
      onSourceChange={onSourceChange}
    />
  );

  expect(mockedConsole).toHaveBeenCalledWith('Invalid source data "date" for enabled configurator');
  expect(screen.queryByText('Update replacement')).not.toBeInTheDocument();
  mockedConsole.mockRestore();
});
