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

  await renderWithProviders(<AttributeTargetParameters target={attributeTarget} onTargetChange={handleTargetChange} />);

  userEvent.click(screen.getByLabelText('pim_common.channel'));
  userEvent.click(screen.getByText('[print]'));

  expect(handleTargetChange).toHaveBeenCalledWith({
    ...attributeTarget,
    channel: 'print',
  });
});

test('it can change the target locale', async () => {
  const handleTargetChange = jest.fn();

  await renderWithProviders(<AttributeTargetParameters target={attributeTarget} onTargetChange={handleTargetChange} />);

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
      onTargetChange={handleTargetChange}
    />
  );

  userEvent.click(screen.getByLabelText('pim_common.locale'));

  expect(screen.getByTitle('English (United States)')).toBeInTheDocument();
  expect(screen.getByTitle('German (Germany)')).toBeInTheDocument();
  expect(screen.queryByTitle('Français (France)')).not.toBeInTheDocument();
});
