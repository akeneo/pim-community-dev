import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {OperatorSelector} from './OperatorSelector';

const availableOperators = ['IN AT LEAST ONE LOCALE', 'IN ALL LOCALES'];

test('it displays the selected operator', () => {
  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="IN AT LEAST ONE LOCALE"
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(
    screen.getByText('pim_enrich.export.product.filter.quality-score.operators.IN AT LEAST ONE LOCALE')
  ).toBeInTheDocument();
});

test('it notifies when the operator is changed', () => {
  const onOperatorChange = jest.fn();

  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="IN AT LEAST ONE LOCALE"
      onChange={onOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(screen.getByText('pim_enrich.export.product.filter.quality-score.operators.IN ALL LOCALES'));

  expect(onOperatorChange).toHaveBeenCalledWith('IN ALL LOCALES');
});

test('it displays validation errors if any', () => {
  const myErrorMessage = 'My message.';

  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="IN AT LEAST ONE LOCALE"
      onChange={() => {}}
      validationErrors={[
        {
          messageTemplate: myErrorMessage,
          parameters: {},
          message: myErrorMessage,
          propertyPath: '',
          invalidValue: '',
        },
      ]}
    />
  );

  expect(screen.queryByText(myErrorMessage)).toBeInTheDocument();
});
