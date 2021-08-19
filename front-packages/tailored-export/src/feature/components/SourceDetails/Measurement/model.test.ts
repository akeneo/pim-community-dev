import {isMeasurementSource, MeasurementSource} from './model';

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
