import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {ReferenceEntitySelector} from '../ReferenceEntity/ReferenceEntitySelector';
import {renderWithProviders} from 'feature/tests';

jest.mock('../../../hooks/useReferenceEntityAttributes');

test('it can change the selection type to "attribute"', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntitySelector
      referenceEntityCode="designer"
      selection={{type: 'code'}}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.type.attribute'));
  userEvent.click(screen.getByText('[name]'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'attribute',
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
    <ReferenceEntitySelector
      referenceEntityCode="designer"
      selection={{
        type: 'attribute',
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

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.type.attribute'));
  userEvent.click(screen.getByText('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'code',
  });
});

test('it can change the selection type to "label"', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntitySelector
      referenceEntityCode="designer"
      selection={{
        type: 'code',
      }}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.type.attribute'));
  userEvent.click(screen.getByText('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    type: 'label',
    locale: 'en_US',
  });
});

test('it can change the locale of the label selection', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <ReferenceEntitySelector
      referenceEntityCode="designer"
      selection={{
        type: 'label',
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
    locale: 'fr_FR',
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
  ];

  await renderWithProviders(
    <ReferenceEntitySelector
      referenceEntityCode="designer"
      selection={{type: 'code'}}
      validationErrors={validationErrors}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
});
