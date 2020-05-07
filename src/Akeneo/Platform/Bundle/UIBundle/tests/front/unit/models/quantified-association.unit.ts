import {
  getQuantifiedAssociationCollectionIdentifiers,
  getQuantifiedLinkForIdentifier,
  setQuantifiedAssociationCollection,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';

const collection = {
  PACK: {
    products: [
      {identifier: 'bag', quantity: '3'},
      {identifier: 'cap', quantity: '9'},
    ],
    product_models: [
      {identifier: 'braided-hat', quantity: '17'},
      {identifier: 'socks', quantity: '6'},
    ],
  },
};

describe('quantified association', () => {
  it('should return the identifiers of a quantified association collection', () => {
    const identifiers = getQuantifiedAssociationCollectionIdentifiers(collection, 'PACK');
    expect(identifiers).toEqual({products: ['bag', 'cap'], product_models: ['braided-hat', 'socks']});
  });

  it('should return the quantified link from quantified association collection', () => {
    let link = getQuantifiedLinkForIdentifier(collection, 'PACK', 'products', 'bag');
    expect(link).toEqual({identifier: 'bag', quantity: '3'});

    link = getQuantifiedLinkForIdentifier(collection, 'PACK', 'product_models', 'braided-hat');
    expect(link).toEqual({identifier: 'braided-hat', quantity: '17'});
  });

  it('should throw when no quantified link is found', () => {
    expect(() => getQuantifiedLinkForIdentifier(collection, 'PACK', 'products', 'UNKNOWN')).toThrowError();
  });

  it('should set the provided quantified link among a quantified association collection', () => {
    const updatedCollection = setQuantifiedAssociationCollection(collection, 'PACK', 'products', {
      identifier: 'cap',
      quantity: '10',
    });

    expect(updatedCollection).toEqual({
      PACK: {
        products: [
          {identifier: 'bag', quantity: '3'},
          {identifier: 'cap', quantity: '10'},
        ],
        product_models: [
          {identifier: 'braided-hat', quantity: '17'},
          {identifier: 'socks', quantity: '6'},
        ],
      },
    });
  });
});
