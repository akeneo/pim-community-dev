import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {FileSelector} from './FileSelector';
import {Attribute} from '../../../../models';
import {FetcherContext} from '../../../../contexts';

const attributes = [
  {
    code: 'photo',
    type: 'pim_catalog_file',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  },
];
const fetchers = {
  attribute: {fetchByIdentifiers: (): Promise<Attribute[]> => Promise.resolve<Attribute[]>(attributes)},
  channel: {fetchAll: (): Promise<Channel[]> => Promise.resolve([])},
};

const renderWithProviders = async (node: ReactNode) =>
  await act(async () => void baseRender(<FetcherContext.Provider value={fetchers}>{node})</FetcherContext.Provider>));


test('it can select a key selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <FileSelector selection={{type: 'path'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.key'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'key'});
});

test('it can select a name selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <FileSelector selection={{type: 'key'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.type.name'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'name'});
});

test('it displays validation errors', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
  ];

  await renderWithProviders(
    <FileSelector
      validationErrors={validationErrors}
      selection={{type: 'path'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
});
