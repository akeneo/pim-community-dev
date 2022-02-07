import React from 'react';
import {screen} from '@testing-library/react';
import {AttributeTargetParameters} from './AttributeTargetParameters';
import {AttributeTarget} from '../../models';
import {renderWithProviders} from 'feature/tests';
import userEvent from '@testing-library/user-event';

const attributeTarget: AttributeTarget = {
  code: 'description',
  type: 'attribute',
  action: 'set',
  if_empty: 'skip',
  channel: 'ecommerce',
  locale: 'fr_FR',
};

test('it can change the target channel', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters target={attributeTarget} validationErrors={[]} onTargetChange={handleTargetChange} />
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
    <AttributeTargetParameters target={attributeTarget} validationErrors={[]} onTargetChange={handleTargetChange} />
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
      target={{...attributeTarget, code: 'locale_specific'}}
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
      validationErrors={[]}
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));

  expect(screen.getByTitle('English (United States)')).toBeInTheDocument();
  expect(screen.getByTitle('German (Germany)')).toBeInTheDocument();
  expect(screen.queryByTitle('Français (France)')).not.toBeInTheDocument();
});

test('it can change the if_empty case when hitting the checkbox', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(
    <AttributeTargetParameters target={attributeTarget} validationErrors={[]} onTargetChange={handleTargetChange} />
  );

  userEvent.click(screen.getByLabelText('akeneo.tailored_import.data_mapping.target.clear_if_empty'));

  expect(handleTargetChange).toHaveBeenCalledWith({
    ...attributeTarget,
    if_empty: 'clear',
  });
});

test('it displays general attribute error when attribute is not found', async () => {
  await renderWithProviders(
    <AttributeTargetParameters
      target={{...attributeTarget, code: 'unknown'}}
      validationErrors={[
        {
          messageTemplate: 'code error message',
          parameters: {},
          message: '',
          propertyPath: '',
          invalidValue: '',
        },
      ]}
      onTargetChange={jest.fn()}
    />
  );

  expect(screen.getByText('code error message')).toBeInTheDocument();
});

test('it displays a helper when attribute is an identifier', async () => {
  await renderWithProviders(
    <AttributeTargetParameters
      target={{...attributeTarget, code: 'sku'}}
      validationErrors={[]}
      onTargetChange={jest.fn()}
    />
  );

  expect(screen.getByText('akeneo.tailored_import.data_mapping.target.identifier')).toBeInTheDocument();
});
