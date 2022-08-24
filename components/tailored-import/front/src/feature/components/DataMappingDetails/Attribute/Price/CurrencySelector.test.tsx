import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {CurrencySelector} from './CurrencySelector';
import {renderWithProviders} from '../../../../tests';
import {ValidationError} from '@akeneo-pim-community/shared';

test('it displays all currencies when target is not scopable', async () => {
  const onTargetChange = jest.fn();

  renderWithProviders(
    <CurrencySelector value="EUR" validationErrors={[]} onChange={onTargetChange} channelReference={null} />
  );

  await userEvent.click(screen.getByTitle('pim_common.open'));
  expect(screen.getByText('USD')).toBeInTheDocument();
  expect(screen.getByText('DKK')).toBeInTheDocument();

  userEvent.click(screen.getByText('USD'));

  expect(onTargetChange).toHaveBeenCalledWith('USD');
});

test('it displays the currencies related to the channel when target is scopable', async () => {
  const onTargetChange = jest.fn();

  renderWithProviders(
    <CurrencySelector value="EUR" validationErrors={[]} onChange={onTargetChange} channelReference="ecommerce" />
  );

  await userEvent.click(screen.getByTitle('pim_common.open'));
  expect(screen.getByText('USD')).toBeInTheDocument();
  expect(screen.queryByText('DKK')).not.toBeInTheDocument();
});

test('it displays validation errors', async () => {
  const validationErrors: ValidationError[] = [
    {
      messageTemplate: 'error.key.currencies',
      invalidValue: '',
      message: 'this is a currencies error',
      parameters: {},
      propertyPath: '',
    },
  ];

  await renderWithProviders(
    <CurrencySelector
      value="EUR"
      validationErrors={validationErrors}
      onChange={jest.fn()}
      channelReference="ecommerce"
    />
  );

  expect(screen.getByText('error.key.currencies')).toBeInTheDocument();
});
