import React from 'react';
import {screen} from '@testing-library/react';
import {AttributeTargetParameters} from './AttributeTargetParameters';
import {Attribute, AttributeTarget} from '../../models';
import {renderWithProviders} from 'feature/tests';
import userEvent from '@testing-library/user-event';

const attribute: Attribute = {
  type: 'pim_catalog_text',
  code: 'description',
  labels: {fr_FR: 'French name', en_US: 'English name'},
  scopable: true,
  localizable: true,
  is_locale_specific: false,
  available_locales: [],
};

const attributeTarget: AttributeTarget = {
  code: attribute.code,
  type: 'attribute',
  attribute_type: attribute.type,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
  source_configuration: null,
  channel: 'ecommerce',
  locale: 'fr_FR',
};

test('it can change the target channel', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters
      attribute={attribute}
      target={attributeTarget}
      validationErrors={[]}
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleTargetChange).toHaveBeenCalledWith({
    ...attributeTarget,
    channel: 'print',
  });
});

test('it can change the target locale', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters
      attribute={attribute}
      target={attributeTarget}
      validationErrors={[]}
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));
  userEvent.click(screen.getByTitle('Breton'));

  expect(handleTargetChange).toHaveBeenCalledWith({
    ...attributeTarget,
    locale: 'br_FR',
  });
});

test('it allows only available locales when attribute is locale-specific', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters
      attribute={{...attribute, is_locale_specific: true, available_locales: ['fr_FR']}}
      target={attributeTarget}
      validationErrors={[]}
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));

  expect(screen.queryByTitle('English')).not.toBeInTheDocument();
});

test('it allows only available locales on selected channel', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters
      // No fr_FR locale on mobile channel in test fixtures
      target={{...attributeTarget, channel: 'mobile'}}
      attribute={attribute}
      validationErrors={[]}
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));

  expect(screen.getByTitle('English (United States)')).toBeInTheDocument();
  expect(screen.getByTitle('German (Germany)')).toBeInTheDocument();
  expect(screen.queryByTitle('Français (France)')).not.toBeInTheDocument();
});

test('it displays general attribute error when attribute is not found', async () => {
  await renderWithProviders(
    <AttributeTargetParameters
      attribute={attribute}
      target={{...attributeTarget, code: 'unknown'}}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '[code]',
          invalidValue: '',
        },
      ]}
      onTargetChange={jest.fn()}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});
