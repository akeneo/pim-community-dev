import {
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

const quantifiedAssociation = {
  products: [{identifier: 'bag', quantity: 4}],
  product_models: [{identifier: 'braided-hat', quantity: 12}],
};

const rowCollection = [
  {
    quantifiedLink: {identifier: 'bag', quantity: 4},
    productType: ProductType.Product,
    product: null,
  },
  {
    quantifiedLink: {identifier: 'braided-hat', quantity: 12},
    productType: ProductType.ProductModel,
    product: null,
  },
];

describe('quantified association', () => {
  it('should create a row collection from a quantified association collection', () => {
    expect(quantifiedAssociationToRowCollection(quantifiedAssociation)).toEqual(rowCollection);
    //@ts-ignore
    expect(quantifiedAssociationToRowCollection({})).toEqual([]);
  });

  it('should create a quantified association collection from a row collection', () => {
    expect(rowCollectionToQuantifiedAssociation(rowCollection)).toEqual(quantifiedAssociation);
    expect(rowCollectionToQuantifiedAssociation([])).toEqual({products: [], product_models: []});
  });
});
