import {setQuantifiedAssociationCollection} from '../../../../Resources/public/js/product/form/quantified-associations/models/quantified-association';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models/product';

const collection = [
  {
    associationTypeCode: 'PACK',
    productType: ProductType.Product,
    quantity: 3,
    identifier: 'bag',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: ProductType.Product,
    quantity: 9,
    identifier: 'cap',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: ProductType.ProductModel,
    quantity: 17,
    identifier: 'braided-hat',
    product: null,
  },
  {
    associationTypeCode: 'PACK',
    productType: ProductType.ProductModel,
    quantity: 6,
    identifier: 'socks',
    product: null,
  },
];

describe('quantified association', () => {
  it('should set the provided quantified link among a quantified association collection', () => {
    const updatedCollection = setQuantifiedAssociationCollection(collection, 'PACK', ProductType.Product, {
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
