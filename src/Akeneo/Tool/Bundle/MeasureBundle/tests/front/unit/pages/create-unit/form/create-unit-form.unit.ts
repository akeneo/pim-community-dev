'use strict';

import '@testing-library/jest-dom/extend-expect';
import {createUnitFromForm, initializeCreateUnitForm} from 'akeneomeasure/pages/create-unit/form/create-unit-form';

test('It can create an empty form state', () => {
  const state = initializeCreateUnitForm();

  expect(state).toEqual({
    code: '',
    label: '',
    symbol: '',
    operations: [
      {
        operator: 'mul',
        value: '1',
      },
    ],
  });
});

test('It can create an unit from an empty form state', () => {
  const locale = 'en_US';
  const state = initializeCreateUnitForm();
  const unit = createUnitFromForm(state, locale);

  expect(unit).toEqual({
    code: '',
    labels: {
      en_US: '',
    },
    symbol: '',
    convert_from_standard: [
      {
        operator: 'mul',
        value: '1',
      },
    ],
  });
});

test('It can create a measurement family from a form state with values', () => {
  const locale = 'en_US';
  const state = {
    code: 'METER',
    label: 'Meters',
    symbol: 'm',
    operations: [
      {
        operator: 'mul',
        value: '1000',
      },
    ],
  };
  const unit = createUnitFromForm(state, locale);

  expect(unit).toEqual({
    code: 'METER',
    labels: {
      en_US: 'Meters',
    },
    symbol: 'm',
    convert_from_standard: [
      {
        operator: 'mul',
        value: '1000',
      },
    ],
  });
});
