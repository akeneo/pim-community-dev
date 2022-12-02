import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {channels, renderWithProviders} from 'feature/tests';
import {ReferenceEntityAttribute} from 'feature/models';
import {ReferenceEntityAttributeSelection} from '../model';
import {AttributeSelector} from './AttributeSelector';

const attribute: ReferenceEntityAttribute = {
  code: 'name',
  identifier: 'name_1234',
  type: 'text',
  labels: {},
  value_per_channel: false,
  value_per_locale: false,
};

const selection: ReferenceEntityAttributeSelection = {
  type: 'attribute',
  attribute_identifier: 'name_1234',
  attribute_type: 'text',
  reference_entity_code: 'designer',
  channel: null,
  locale: null,
};

test('it can change the channel if the attribute has a value per channel', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AttributeSelector
      attribute={{...attribute, value_per_channel: true}}
      selection={{...selection, channel: 'ecommerce'}}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('[print]'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    channel: 'print',
  });
});

test('it can change the locale if the attribute has a value per locale', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <AttributeSelector
      attribute={{...attribute, value_per_locale: true}}
      selection={{...selection, locale: 'fr_FR'}}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('English (United States)'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    locale: 'en_US',
  });
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.channel',
      invalidValue: '',
      message: 'this is a channel error',
      parameters: {},
      propertyPath: '[channel]',
    },
    {
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
  ];

  await renderWithProviders(
    <AttributeSelector
      attribute={{...attribute, value_per_channel: true, value_per_locale: true}}
      selection={{...selection, channel: 'ecommerce', locale: 'fr_FR'}}
      channels={channels}
      validationErrors={validationErrors}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.channel')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
});
