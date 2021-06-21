import React, {ReactNode} from 'react';
import {act, screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {Channel, renderWithProviders as baseRender, ValidationError} from '@akeneo-pim-community/shared';
import {DateSelector} from './DateSelector';
import {Attribute} from '../../../../models';
import {FetcherContext} from '../../../../contexts';

const attributes = [
  {
    code: 'release_date',
    type: 'pim_catalog_date',
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

test('it can select a date format', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <DateSelector selection={{format: 'yyyy-mm-dd'}} validationErrors={[]} onSelectionChange={onSelectionChange} />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.format'));
  userEvent.click(screen.getByTitle('dd.m.yy'));

  expect(onSelectionChange).toHaveBeenCalledWith({format: 'dd.m.yy'});
});

test('it displays validation errors', async () => {
  const onSelectionChange = jest.fn();
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.format',
      invalidValue: '',
      message: 'this is a format error',
      parameters: {},
      propertyPath: '[format]',
    },
  ];

  await renderWithProviders(
    <DateSelector
      validationErrors={validationErrors}
      selection={{format: 'yyyy-mm-dd'}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('error.key.format')).toBeInTheDocument();
});
