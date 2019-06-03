import {denormalizeCompleteness} from 'akeneoreferenceentity/domain/model/product/completeness';

const completeness = denormalizeCompleteness({
  required: 10,
  complete: 4,
});
const anotherCompleteness = denormalizeCompleteness({
  required: 0,
  complete: 0,
});

describe('akeneo > reference entity > domain > model > product --- completeness', () => {
  test('I can create a new completeness', () => {
    expect(completeness.getCompleteCount()).toEqual(4);
    expect(completeness.getRequiredCount()).toEqual(10);
    expect(completeness.isComplete()).toEqual(false);
    expect(completeness.hasCompleteItems()).toEqual(true);
    expect(completeness.getRatio()).toEqual(40);
    expect(anotherCompleteness.getRatio()).toEqual(0);
    expect(completeness.normalize()).toEqual({complete: 4, required: 10});
  });
});
