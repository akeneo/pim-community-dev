import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {CodeLabelCollectionSelector, getDefaultCodeLabelCollectionSelection} from './CodeLabelCollectionSelector';
import {renderWithProviders} from '../../../tests';

test('it displays a type dropdown and a separator dropdown when the selection type is code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.title')
  ).toBeInTheDocument();
  expect(screen.getByText('pim_common.code')).toBeInTheDocument();
  expect(screen.queryByText('pim_common.locale')).not.toBeInTheDocument();
});

test('it displays a locale dropdown when the selection type is label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  expect(screen.getByText('pim_common.type')).toBeInTheDocument();
  expect(screen.getByText('pim_common.locale')).toBeInTheDocument();

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'fr_FR', separator: ','});
});

test('it can select a label selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'code', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'en_US', separator: ','});
});

test('it can select a code selection type', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByText('pim_common.type'));
  userEvent.click(screen.getByTitle('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'code', separator: ','});
});

test('it can select a collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={[]}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByTitle('akeneo.syndication.data_mapping_details.sources.selection.collection_separator.semicolon')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({type: 'label', locale: 'en_US', separator: ';'});
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.separator',
      invalidValue: '',
      message: 'this is a separator error',
      parameters: {},
      propertyPath: '[separator]',
    },
    {
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
  ];

  await renderWithProviders(
    <CodeLabelCollectionSelector
      validationErrors={validationErrors}
      selection={{type: 'label', locale: 'en_US', separator: ','}}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByRole('alert')).toBeInTheDocument();
});

test('it returns a default code label collection selection', () => {
  expect(getDefaultCodeLabelCollectionSelection()).toStrictEqual({
    type: 'code',
    separator: ',',
  });
});
