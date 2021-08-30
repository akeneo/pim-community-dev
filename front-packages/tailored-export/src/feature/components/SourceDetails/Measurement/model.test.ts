import {isMeasurementSource, MeasurementSource, isMeasurementConversionOperation} from './model';

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
