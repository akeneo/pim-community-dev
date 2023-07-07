import {createUnitFromForm, initializeCreateUnitForm, validateCreateUnitForm} from './create-unit-form';
import {MeasurementFamily} from '../../../model/measurement-family';

test('It can create an empty form state', () => {
  const state = initializeCreateUnitForm();

  expect(state).toEqual({
    code: '',
    label: '',
    symbol: '',
    operations: [
      {
        operator: 'mul',
        value: '',
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
        value: '',
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

test('It validate the uniqueness of the unit code', () => {
  const translator = (id: string) => id;

  const measurementFamily: MeasurementFamily = {
    code: 'foo',
    labels: {},
    standard_unit_code: 'METER',
    units: [
      {
        code: 'METER',
        labels: {},
        convert_from_standard: [
          {
            operator: 'mul',
            value: '1',
          },
        ],
        symbol: 'm',
      },
    ],
    is_locked: false,
  };

  const state = {
    ...initializeCreateUnitForm(),
    code: 'SOMETHING_ELSE',
  };

  expect(validateCreateUnitForm(state, measurementFamily, translator)).toEqual([]);
});

test('It returns an error if the code already exists', () => {
  const translator = jest.fn().mockImplementation((id: string) => id);

  const measurementFamily: MeasurementFamily = {
    code: 'foo',
    labels: {},
    standard_unit_code: 'METER',
    units: [
      {
        code: 'METER',
        labels: {},
        convert_from_standard: [
          {
            operator: 'mul',
            value: '1',
          },
        ],
        symbol: 'm',
      },
    ],
    is_locked: false,
  };

  const state = {
    ...initializeCreateUnitForm(),
    code: 'METER',
  };

  expect(validateCreateUnitForm(state, measurementFamily, translator)).toEqual([
    {
      propertyPath: 'code',
      message: 'measurements.validation.unit.code.must_be_unique',
      messageTemplate: 'measurements.validation.unit.code.must_be_unique',
      parameters: {},
      invalidValue: 'METER',
    },
  ]);
  expect(translator).toHaveBeenCalledWith('measurements.validation.unit.code.must_be_unique');
});
