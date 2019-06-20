import {denormalizeCompleteness} from 'akeneoreferenceentity/domain/model/product/completeness';

const completeProductModel = denormalizeCompleteness({
  completeChildren: 10,
  totalChildren: 10,
  ratio: 0,
});
const incompleteProductModel = denormalizeCompleteness({
  completeChildren: 4,
  totalChildren: 10,
  ratio: 0,
});

const completeProduct = denormalizeCompleteness({
  completeChildren: 0,
  totalChildren: 0,
  ratio: 100,
});
const incompleteProduct = denormalizeCompleteness({
  completeChildren: 0,
  totalChildren: 0,
  ratio: 58,
});

describe('akeneo > reference entity > domain > model > product --- completeness', () => {
  test('I can create a new complete product', () => {
    expect(incompleteProductModel.normalize()).toEqual({completeChildren: 4, totalChildren: 10, ratio: 0});
    expect(incompleteProductModel.getCompleteChildren()).toEqual(4);
    expect(incompleteProductModel.getTotalChildren()).toEqual(10);
    expect(incompleteProductModel.hasCompleteItems()).toEqual(true);
    expect(incompleteProductModel.getRatio()).toEqual(0);
    expect(incompleteProductModel.isComplete()).toEqual(false);
    expect(completeProductModel.isComplete()).toEqual(true);
    expect(completeProductModel.hasCompleteItems()).toEqual(true);

    expect(completeProduct.normalize()).toEqual({completeChildren: 0, totalChildren: 0, ratio: 100});
    expect(completeProduct.getRatio()).toEqual(100);
    expect(completeProduct.isComplete()).toEqual(true);
    expect(incompleteProduct.isComplete()).toEqual(false);
    expect(incompleteProduct.hasCompleteItems()).toEqual(true);
  });
});
