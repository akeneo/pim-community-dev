import {
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  isQuantifiedAssociationEmpty,
  newAndUpdatedQuantifiedAssociationsCount,
  hasUpdatedQuantifiedAssociations,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models';

const quantifiedAssociation = {
  products: [{identifier: 'bag', quantity: 4}],
  product_models: [{identifier: 'braided-hat', quantity: 12}],
};

const parentQuantifiedAssociation = {
  products: [{identifier: 'bag', quantity: 6}],
  product_models: [{identifier: 'braided-hat', quantity: 8}],
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
    expect(quantifiedAssociationToRowCollection({products: [], product_models: []})).toEqual([]);
  });

  it('should create a quantified association collection from a row collection', () => {
    expect(rowCollectionToQuantifiedAssociation(rowCollection)).toEqual(quantifiedAssociation);
    expect(rowCollectionToQuantifiedAssociation([])).toEqual({products: [], product_models: []});
  });

  it('can tell if a quantified association is empty', () => {
    expect(isQuantifiedAssociationEmpty(quantifiedAssociation)).toBe(false);
    expect(isQuantifiedAssociationEmpty({product_models: [], products: []})).toBe(true);
  });

  it('should tell the count of new and updated quantified associations', () => {
    expect(newAndUpdatedQuantifiedAssociationsCount(parentQuantifiedAssociation, quantifiedAssociation)).toEqual(2);
    expect(newAndUpdatedQuantifiedAssociationsCount(quantifiedAssociation, quantifiedAssociation)).toEqual(0);
  });

  it('should tell if the provided quantified association has some updated quantities', () => {
    expect(hasUpdatedQuantifiedAssociations(parentQuantifiedAssociation, quantifiedAssociation)).toEqual(true);
    expect(hasUpdatedQuantifiedAssociations(quantifiedAssociation, quantifiedAssociation)).toEqual(false);
  });
});
