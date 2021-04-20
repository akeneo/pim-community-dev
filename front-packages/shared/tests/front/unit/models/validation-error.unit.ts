import {
  ValidationError,
  filterErrors,
  partitionErrors,
  formatParameters,
} from '../../../../src/models/validation-error';

const createValidationError = (propertyPath: string, message: string = 'error'): ValidationError => {
  return {
    propertyPath,
    message,
    messageTemplate: '',
    parameters: {},
    invalidValue: null,
  };
};

it('should filter errors based on the property', () => {
  const errors = [createValidationError('code', 'bad code'), createValidationError('labels', 'bad labels')];

  expect(filterErrors(errors, 'code')).toEqual([
    {
      propertyPath: '',
      message: 'bad code',
      messageTemplate: '',
      parameters: {},
      invalidValue: null,
    },
  ]);
});

it('should partition the errors based on a list of filters', () => {
  const errors = [
    createValidationError('code'),
    createValidationError('labels'),
    createValidationError('labels[fr_FR]'),
    createValidationError('labels[en_US]'),
    createValidationError('units'),
  ];
  const filters = [
    (error: ValidationError) => error.propertyPath.startsWith('labels'),
    (error: ValidationError) => error.propertyPath.startsWith('code'),
  ];

  expect(partitionErrors(errors, filters)).toEqual([[errors[1], errors[2], errors[3]], [errors[0]], [errors[4]]]);
});

it('should format error parameters correctly, removing {{ }}', () => {
  const errors = [
    {
      propertyPath: '',
      message: 'bad code',
      messageTemplate: '',
      parameters: {'{{ limit }}': '100'},
      invalidValue: null,
    },
  ];

  expect(formatParameters(errors)).toEqual([
    {
      propertyPath: '',
      message: 'bad code',
      messageTemplate: '',
      parameters: {limit: '100'},
      invalidValue: null,
    },
  ]);
});
