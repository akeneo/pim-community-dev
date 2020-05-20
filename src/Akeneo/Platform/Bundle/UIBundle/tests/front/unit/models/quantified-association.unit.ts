import {
  isQuantifiedAssociationCollectionEmpty,
  quantifiedAssociationCollectionToRowCollection,
  rowCollectionToQuantifiedAssociationCollection,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

const quantifiedAssociationCollection = {
  PACK: {
    products: [{identifier: 'bag', quantity: 4}],
    product_models: [],
  },
  CROSS_SELL: {
    products: [{identifier: 'sock', quantity: 8}],
    product_models: [{identifier: 'braided-hat', quantity: 12}],
  },
};

const rowCollection = [
  {identifier: 'bag', quantity: 4, productType: ProductType.Product, associationTypeCode: 'PACK', product: null},
  {
    identifier: 'sock',
    quantity: 8,
    productType: ProductType.Product,
    associationTypeCode: 'CROSS_SELL',
    product: null,
  },
  {
    identifier: 'braided-hat',
    quantity: 12,
    productType: ProductType.ProductModel,
    associationTypeCode: 'CROSS_SELL',
    product: null,
  },
];

describe('quantified association', () => {
  it('should tell if a quantified association collection is empty', () => {
    expect(isQuantifiedAssociationCollectionEmpty(quantifiedAssociationCollection)).toEqual(false);
    expect(isQuantifiedAssociationCollectionEmpty({})).toEqual(true);
    expect(isQuantifiedAssociationCollectionEmpty({PACK: {products: [], product_models: []}})).toEqual(true);
  });

  it('should create a row collection from a quantified association collection', () => {
    expect(quantifiedAssociationCollectionToRowCollection(quantifiedAssociationCollection)).toEqual(rowCollection);
  });

  it('should create a quantified association collection from a row collection', () => {
    expect(rowCollectionToQuantifiedAssociationCollection(rowCollection)).toEqual(quantifiedAssociationCollection);
  });
});
