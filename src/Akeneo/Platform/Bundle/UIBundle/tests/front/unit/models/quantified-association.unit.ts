import {
  ProductType,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  isQuantifiedAssociationEmpty,
  newAndUpdatedQuantifiedAssociationsCount,
  hasUpdatedQuantifiedAssociations,
} from '../../../../Resources/public/js/product/form/quantified-associations/models';

const quantifiedAssociation = {
  products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 4}],
  product_models: [{identifier: 'braided-hat', quantity: 12}],
};

const parentQuantifiedAssociation = {
  products: [{uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 6}],
  product_models: [{identifier: 'braided-hat', quantity: 8}],
};

const rowCollection = [
  {
    quantifiedLink: {uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 4},
    productType: ProductType.Product,
    product: null,
    errors: [],
  },
  {
    quantifiedLink: {identifier: 'braided-hat', quantity: 12},
    productType: ProductType.ProductModel,
    product: null,
    errors: [],
  },
];
const rowCollectionWithError = [
  {
    quantifiedLink: {uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb', quantity: 4},
    productType: ProductType.Product,
    product: null,
    errors: [
      {
        propertyPath: 'quantity',
        messageTemplate: 'an.error',
        parameters: {},
        message: 'an error',
        invalidValue: '10000',
      },
    ],
  },
  {
    quantifiedLink: {identifier: 'braided-hat', quantity: 12},
    productType: ProductType.ProductModel,
    product: null,
    errors: [
      {
        propertyPath: 'quantity',
        messageTemplate: 'an.error',
        parameters: {},
        message: 'an error',
        invalidValue: '10000',
      },
    ],
  },
];

describe('quantified association', () => {
  it('should create a row collection from a quantified association collection', () => {
    expect(quantifiedAssociationToRowCollection(quantifiedAssociation, [])).toEqual(rowCollection);
    expect(quantifiedAssociationToRowCollection({products: [], product_models: []}, [])).toEqual([]);
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

  it('adds errors to the matching row', () => {
    expect(
      quantifiedAssociationToRowCollection(quantifiedAssociation, [
        {
          propertyPath: '.products[0].quantity',
          messageTemplate: 'an.error',
          parameters: {},
          message: 'an error',
          invalidValue: '10000',
        },
        {
          propertyPath: '.product_models[0].quantity',
          messageTemplate: 'an.error',
          parameters: {},
          message: 'an error',
          invalidValue: '10000',
        },
      ])
    ).toEqual(rowCollectionWithError);
  });
});
