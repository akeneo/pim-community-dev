import React from 'react';
import {InputErrors} from '@akeneo-pim-community/shared';
import {renderWithProviders} from '../utils';

test('It displays input errors', () => {
  const {getByText} = renderWithProviders(
    <InputErrors
      errors={[
        {
          propertyPath: 'quantity',
          messageTemplate: 'an.error',
          parameters: {
            limit: '255',
          },
          message: 'an error',
          invalidValue: '10000',
        },
      ]}
    />
  );

  expect(getByText('an.error')).toBeInTheDocument();
});
