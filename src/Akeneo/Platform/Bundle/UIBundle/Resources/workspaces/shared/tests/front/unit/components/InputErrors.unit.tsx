import {inputErrors} from '@akeneo-pim-community/shared';

const errors = [
  {
    propertyPath: 'quantity',
    messageTemplate: 'an.error',
    parameters: {
      limit: '255',
    },
    message: 'an error',
    invalidValue: '10000',
  },
  {
    propertyPath: 'symbol',
    messageTemplate: 'an.error',
    parameters: {
      limit: '255',
    },
    message: 'an error',
    invalidValue: '10000',
  },
];

test('It displays input errors', () => {
  const errorsAsHelpers = inputErrors(() => '', errors);

  expect(errorsAsHelpers).toHaveLength(2);
});
