import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {renderWithProviders} from 'feature/tests';
import {ReferenceEntityCollectionSelector} from './ReferenceEntityCollectionSelector';

jest.mock('../../../hooks/useReferenceEntityAttributes');

test('it can change the selection type to "attribute"', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{type: 'code', separator: ','}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByText('[name]'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'attribute',
    separator: ',',
    attribute_identifier: 'name_1234',
    attribute_type: 'text',
    reference_entity_code: 'designer',
    channel: 'ecommerce',
    locale: null,
  });
});

test('it can change the selection type to "code"', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{
        type: 'attribute',
        separator: ';',
        attribute_identifier: 'name_1234',
        attribute_type: 'text',
        reference_entity_code: 'designer',
        channel: 'ecommerce',
        locale: null,
      }}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByText('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'code',
    separator: ';',
  });
});

test('it can change the selection type to "label"', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{
        type: 'code',
        separator: ';',
      }}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.type'));
  userEvent.click(screen.getByText('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'en_US',
    separator: ';',
  });
});

test('it can change the locale of the label selection', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{
        type: 'label',
        separator: ';',
        locale: 'en_US',
      }}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    separator: ';',
    locale: 'fr_FR',
  });
});

test('it can change the collection separator', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{
        type: 'code',
        separator: ';',
      }}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByText('akeneo.tailored_export.column_details.sources.selection.collection_separator.title')
  );
  userEvent.click(
    screen.getByTitle('akeneo.tailored_export.column_details.sources.selection.collection_separator.pipe')
  );

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'code',
    separator: '|',
  });
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.type',
      invalidValue: '',
      message: 'this is a type error',
      parameters: {},
      propertyPath: '[type]',
    },
    {
      messageTemplate: 'error.key.separator',
      invalidValue: '',
      message: 'this is a separator error',
      parameters: {},
      propertyPath: '[separator]',
    },
  ];

  await renderWithProviders(
    <ReferenceEntityCollectionSelector
      referenceEntityCode="designer"
      selection={{type: 'code', separator: ','}}
      validationErrors={validationErrors}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.separator')).toBeInTheDocument();
});
