import {
  isMeasurementSource,
  MeasurementSource,
  isMeasurementConversionOperation,
  isDefaultMeasurementConversionOperation,
  getDefaultMeasurementConversionOperation,
  isMeasurementRoundingOperation,
  isDefaultMeasurementRoundingOperation,
  getDefaultMeasurementRoundingOperation,
} from './model';

const source: MeasurementSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'unit_code'},
};

test('it validates that something is a measurement source', () => {
  expect(isMeasurementSource(source)).toEqual(true);

  expect(
    isMeasurementSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
        measurement_conversion: {
          type: 'measurement_conversion',
          target_unit_code: null,
        },
        measurement_rounding: {
          type: 'measurement_rounding',
          rounding_type: 'standard',
          precision: 2,
        },
      },
    })
  ).toEqual(true);

  expect(
    // @ts-expect-error invalid operations
    isMeasurementSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});

test('it can validate that it is a measurement conversion operation', async () => {
  expect(
    isMeasurementConversionOperation({
      type: 'measurement_conversion',
      target_unit_code: 'meter',
    })
  ).toBe(true);
  expect(
    isMeasurementConversionOperation({
      type: 'measurement_conversion',
      target_unit_code: null,
    })
  ).toBe(true);
  expect(
    isMeasurementConversionOperation({
      type: 'test',
      target_unit_code: null,
    })
  ).toBe(false);
  expect(isMeasurementConversionOperation(undefined)).toBe(false);
});

test('it validates that it is a default conversion operation', async () => {
  expect(isDefaultMeasurementConversionOperation(getDefaultMeasurementConversionOperation())).toBe(true);
});

test('it validates that it is a measurement rounding operation', async () => {
  expect(
    isMeasurementRoundingOperation({
      type: 'measurement_rounding',
      rounding_type: 'no_rounding',
    })
  ).toBe(true);
  expect(
    isMeasurementRoundingOperation({
      type: 'measurement_rounding',
      rounding_type: 'no_rounding',
    })
  ).toBe(true);
  expect(
    isMeasurementRoundingOperation({
      type: 'measurement_rounding',
      rounding_type: 'standard',
    })
  ).toBe(true);
  expect(
    isMeasurementRoundingOperation({
      type: 'test',
      rounding_type: 'unsupported_type',
    })
  ).toBe(false);
  expect(isMeasurementRoundingOperation(undefined)).toBe(false);
});

test('it validates that it is a default rounding operation', async () => {
  expect(isDefaultMeasurementRoundingOperation(getDefaultMeasurementRoundingOperation())).toBe(true);
});
