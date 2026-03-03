import {ValidationError, filterErrors, partitionErrors, formatParameters, getErrorsForPath} from './validation-error';

const createValidationError = (propertyPath: string, message: string = 'error'): ValidationError => ({
  propertyPath,
  message,
  messageTemplate: '',
  parameters: {},
  invalidValue: null,
});

test('it should filter errors based on the property path and update the property path accordingly', () => {
  const errors = [
    createValidationError('[code]', 'bad code'),
    createValidationError('[code][value]', 'bad value in code'),
    createValidationError('[labels]', 'bad labels'),
  ];

  expect(filterErrors(errors, '[code]')).toEqual([
    createValidationError('', 'bad code'),
    createValidationError('[value]', 'bad value in code'),
  ]);
});

test('it should return errors based on the exact property path and leave it untouched', () => {
  const errors = [
    createValidationError('[code]', 'bad code'),
    createValidationError('[code][value]', 'bad value in code'),
    createValidationError('[labels]', 'bad labels'),
  ];

  expect(getErrorsForPath(errors, '[code]')).toEqual([createValidationError('[code]', 'bad code')]);
});

test('it should partition the errors based on a list of filters', () => {
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

test('it should format error parameters correctly, removing {{ }}', () => {
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
