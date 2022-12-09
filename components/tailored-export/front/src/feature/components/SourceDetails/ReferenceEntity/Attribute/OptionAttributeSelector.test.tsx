import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {ValidationError} from '@akeneo-pim-community/shared';
import {channels, renderWithProviders} from 'feature/tests';
import {ReferenceEntityAttribute} from 'feature/models';
import {ReferenceEntityOptionAttributeSelection} from '../model';
import {OptionAttributeSelector} from './OptionAttributeSelector';

const attribute: ReferenceEntityAttribute = {
  code: 'size',
  identifier: 'size_1234',
  type: 'option',
  labels: {},
  value_per_channel: false,
  value_per_locale: false,
};

const selection: ReferenceEntityOptionAttributeSelection = {
  type: 'attribute',
  attribute_identifier: 'size_1234',
  attribute_type: 'option',
  reference_entity_code: 'designer',
  channel: null,
  locale: null,
  option_selection: {type: 'label', locale: 'en_US'},
};

test('it can change the option selection type to code', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <OptionAttributeSelector
      attribute={attribute}
      selection={selection}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.reference_entity.option_type')
  );
  userEvent.click(screen.getByTitle('pim_common.code'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    option_selection: {
      type: 'code',
    },
  });
});

test('it can change the option selection type to label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <OptionAttributeSelector
      attribute={attribute}
      selection={{...selection, option_selection: {type: 'code'}}}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  userEvent.click(
    screen.getByLabelText('akeneo.tailored_export.column_details.sources.selection.reference_entity.option_type')
  );
  userEvent.click(screen.getByTitle('pim_common.label'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    option_selection: {
      type: 'label',
      locale: 'en_US',
    },
  });
});

test('it can change the option locale when selection is label', async () => {
  const onSelectionChange = jest.fn();

  await renderWithProviders(
    <OptionAttributeSelector
      attribute={attribute}
      selection={selection}
      channels={channels}
      validationErrors={[]}
      onSelectionChange={onSelectionChange}
    />
  );

  const [, localeSelectInput] = screen.getAllByTitle('pim_common.open');
  userEvent.click(localeSelectInput);
  userEvent.click(screen.getByText('Français'));

  expect(onSelectionChange).toHaveBeenCalledWith({
    ...selection,
    option_selection: {
      type: 'label',
      locale: 'fr_FR',
    },
  });
});

test('it throws when the selection is invalid', async () => {
  const mockedConsole = jest.spyOn(console, 'error').mockImplementation();
  const invalidSelection = {...selection, attribute_type: 'image'};

  await expect(async () => {
    await renderWithProviders(
      <OptionAttributeSelector
        attribute={attribute}
        selection={invalidSelection}
        channels={channels}
        validationErrors={[]}
        onSelectionChange={jest.fn()}
      />
    );
  }).rejects.toThrow('Invalid selection type for Option Attribute Selector');

  mockedConsole.mockRestore();
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
      messageTemplate: 'error.key.locale',
      invalidValue: '',
      message: 'this is a locale error',
      parameters: {},
      propertyPath: '[locale]',
    },
  ];

  await renderWithProviders(
    <OptionAttributeSelector
      attribute={attribute}
      selection={selection}
      channels={channels}
      validationErrors={validationErrors}
      onSelectionChange={jest.fn()}
    />
  );

  expect(screen.getByText('error.key.type')).toBeInTheDocument();
  expect(screen.getByText('error.key.locale')).toBeInTheDocument();
});
