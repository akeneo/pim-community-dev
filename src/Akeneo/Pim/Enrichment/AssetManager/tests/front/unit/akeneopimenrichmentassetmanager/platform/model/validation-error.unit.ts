import {getValidationErrorsForAttribute} from 'akeneoassetmanager/platform/model/validation-error';
import {isValidErrorCollection, denormalizeErrorCollection} from 'akeneoassetmanager/platform/model/validation-error';

test('It should get the error for the attribute and the context given', () => {
  const attributeCode = 'packshot';
  const context = {
    locale: 'en_US',
    channel: 'ecommerce',
  };
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'This value is not valid.',
      channel: 'ecommerce',
    },
  ];
  expect(getValidationErrorsForAttribute(attributeCode, context, errors)).toEqual(errors);
});

test('It should return true if the error collection is well formatted', () => {
  const errors = [
    {
      attribute: 'packshot',
      locale: null,
      message: 'Wrong packshot',
      scope: null,
    },
  ];

  expect(isValidErrorCollection(errors)).toEqual(true);
});

test('It should return false if the error collection is not an array', () => {
  const errors = 'wrong_errors';
  expect(isValidErrorCollection(errors)).toEqual(false);
});

test('It should return false if the attribute is not well formated or does not exist', () => {
  const errors = [
    {
      locale: 'en_US',
      message: 'Wrong packshot',
      scope: 'ecommerce',
    },
  ];

  expect(isValidErrorCollection(errors)).toEqual(false);
});

test('It should return false if the locale is not well formated or does not exist', () => {
  const errors = [
    {
      attribute: 'packshot',
      message: 'Wrong attribute',
      scope: 'ecommerce',
    },
  ];

  expect(isValidErrorCollection(errors)).toEqual(false);
});

test('It should return false if the message is not well formated or does not exist', () => {
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      scope: 'ecommerce',
    },
  ];

  expect(isValidErrorCollection(errors)).toEqual(false);
});

test('It should return false if the scope is not well formated or does not exist', () => {
  const errors = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      scope: 1,
    },
  ];

  expect(isValidErrorCollection(errors)).toEqual(false);
});

test('It should denormalize the error collection', () => {
  const normalizedErrorCollection = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      scope: 'ecommerce',
    },
  ];
  const errorCollection = [
    {
      attribute: 'packshot',
      locale: 'en_US',
      message: 'Wrong packshot',
      channel: 'ecommerce',
      scope: 'ecommerce',
    },
  ];

  expect(denormalizeErrorCollection(normalizedErrorCollection)).toEqual(errorCollection);
});
