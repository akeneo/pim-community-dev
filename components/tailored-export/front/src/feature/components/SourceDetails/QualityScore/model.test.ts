import {getDefaultQualityScoreSource, isQualityScoreSource, QualityScoreSource} from './model';

test('it tells if it is a Quality Score source', () => {
  const source: QualityScoreSource = {
    uuid: 'test_id',
    code: 'quality_score',
    type: 'property',
    locale: 'en_US',
    channel: 'print',
    operations: {},
    selection: {type: 'code'},
  };

  expect(isQualityScoreSource(source)).toBe(true);

  expect(
    // @ts-expect-error invalid code
    isQualityScoreSource({
      ...source,
      code: 'parent',
    })
  ).toEqual(false);
});

test('it initializes a default Quality Score source', () => {
  expect(getDefaultQualityScoreSource('ecommerce', 'fr_FR')).toStrictEqual({
    uuid: expect.any(String),
    code: 'quality_score',
    type: 'property',
    locale: 'fr_FR',
    channel: 'ecommerce',
    operations: {},
    selection: {type: 'code'},
  });
});
