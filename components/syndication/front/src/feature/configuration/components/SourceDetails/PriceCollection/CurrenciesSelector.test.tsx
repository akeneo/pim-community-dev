import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {CurrenciesSelector} from './CurrenciesSelector';
import {renderWithProviders} from '../../../tests';
import {ValidationError} from '@akeneo-pim-community/shared';

test('it displays all currencies when source is not scopable', async () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CurrenciesSelector value={['EUR']} validationErrors={[]} onChange={onSourceChange} channelReference={null} />
  );

  await userEvent.click(screen.getByTitle('pim_common.open'));
  expect(screen.getByText('USD')).toBeInTheDocument();
  expect(screen.getByText('DKK')).toBeInTheDocument();

  userEvent.click(screen.getByText('USD'));

  expect(onSourceChange).toHaveBeenCalledWith(['EUR', 'USD']);
});

test('it displays the currencies related to the channel when source is scopable', async () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CurrenciesSelector value={['EUR']} validationErrors={[]} onChange={onSourceChange} channelReference="ecommerce" />
  );

  await userEvent.click(screen.getByTitle('pim_common.open'));
  expect(screen.getByText('USD')).toBeInTheDocument();
  expect(screen.queryByText('DKK')).not.toBeInTheDocument();
});

test('it can remove a currency', async () => {
  const onSourceChange = jest.fn();

  renderWithProviders(
    <CurrenciesSelector value={['EUR']} validationErrors={[]} onChange={onSourceChange} channelReference="ecommerce" />
  );

  await userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByTitle('pim_common.remove'));

  expect(onSourceChange).toHaveBeenCalledWith([]);
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
    <CurrenciesSelector
      value={['EUR']}
      validationErrors={validationErrors}
      onChange={jest.fn()}
      channelReference="ecommerce"
    />
  );

  expect(screen.getByText('error.key.currencies')).toBeInTheDocument();
});
