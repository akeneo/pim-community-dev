import React from 'react';
import {screen} from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {OperatorSelector} from './OperatorSelector';

const availableOperators = ['ALL', 'LOWER THAN ON ALL LOCALES'];

test('it displays the selected operator', () => {
  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="ALL"
      onChange={() => {}}
      validationErrors={[]}
    />
  );

  expect(screen.getByText('pim_enrich.export.product.filter.completeness.operators.ALL')).toBeInTheDocument();
});

test('it notifies when the operator is changed', () => {
  const onOperatorChange = jest.fn();

  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="ALL"
      onChange={onOperatorChange}
      validationErrors={[]}
    />
  );

  userEvent.click(screen.getByTitle('pim_common.open'));
  userEvent.click(
    screen.getByText('pim_enrich.export.product.filter.completeness.operators.LOWER THAN ON ALL LOCALES')
  );

  expect(onOperatorChange).toHaveBeenCalledWith('LOWER THAN ON ALL LOCALES');
});

test('it displays validations errors if any', () => {
  const myErrorMessage = 'My message.';

  renderWithProviders(
    <OperatorSelector
      availableOperators={availableOperators}
      operator="ALL"
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
