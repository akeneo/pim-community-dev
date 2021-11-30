import {FamilyVariantSource, isFamilyVariantSource} from './model';

const source: FamilyVariantSource = {
  uuid: '123',
  code: 'family_variant',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
};

test('it validates that something is a family variant source', () => {
  expect(isFamilyVariantSource(source)).toEqual(true);

  expect(
    isFamilyVariantSource({
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
    isFamilyVariantSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
