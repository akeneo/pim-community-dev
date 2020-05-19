import {
  getQuantifiedAssociationCollectionIdentifiers,
  getQuantifiedLinkForIdentifier,
  setQuantifiedAssociationCollection,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';

const collection = [
  {
    associationTypeCode: 'PACK',
    productType: 'product',
    quantity: 3,
    identifier: 'bag',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: 'product',
    quantity: 9,
    identifier: 'cap',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: 'product_model',
    quantity: 17,
    identifier: 'braided-hat',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: 'product_model',
    quantity: 6,
    identifier: 'socks',
    product: null,
  },
];

describe('quantified association', () => {
  it('should return the identifiers of a quantified association collection', () => {
    const identifiers = getQuantifiedAssociationCollectionIdentifiers(collection, 'PACK');
    expect(identifiers).toEqual({products: ['bag', 'cap'], product_models: ['braided-hat', 'socks']});
  });

  it('should return the quantified link from quantified association collection', () => {
    let link = getQuantifiedLinkForIdentifier(collection, 'PACK', 'products', 'bag');
    expect(link).toEqual({identifier: 'bag', quantity: 3});

    link = getQuantifiedLinkForIdentifier(collection, 'PACK', 'product_models', 'braided-hat');
    expect(link).toEqual({identifier: 'braided-hat', quantity: 17});
  });

  it('should set the provided quantified link among a quantified association collection', () => {
    const updatedCollection = setQuantifiedAssociationCollection(collection, 'PACK', 'product', {
      identifier: 'cap',
      quantity: 10,
    });

    expect(updatedCollection).toEqual([
      {
        associationTypeCode: 'PACK',
        productType: 'product',
        quantity: 3,
        identifier: 'bag',
        product: null,
      },
      {
        associationTypeCode: 'PACK',
        productType: 'product',
        quantity: 10,
        identifier: 'cap',
        product: null,
      },
      {
        associationTypeCode: 'PACK',
        productType: 'product_model',
        quantity: 17,
        identifier: 'braided-hat',
        product: null,
      },
      {
        associationTypeCode: 'PACK',
        productType: 'product_model',
        quantity: 6,
        identifier: 'socks',
        product: null,
      },
    ]);
  });
});
