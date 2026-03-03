import {
  addProductToRows,
  addRowsToCollection,
  filterOnLabelOrIdentifier,
  getAssociationIdentifiers,
  removeRowFromCollection,
  updateRowInCollection,
} from '../../../../Resources/public/js/product/form/quantified-associations/models/row';
import {ProductType} from '../../../../Resources/public/js/product/form/quantified-associations/models/product';

const productRow = {
  productType: ProductType.Product,
  quantifiedLink: {quantity: 3, uuid: '3fa79b52-5900-49e8-a197-1181f58ec3cb'},
  product: null,
  errors: [],
};

const productModelRow = {
  productType: ProductType.ProductModel,
  quantifiedLink: {quantity: 17, identifier: 'braided-hat'},
  product: null,
  errors: [],
};

const product = {
  id: '3fa79b52-5900-49e8-a197-1181f58ec3cb',
  identifier: 'bag',
  label: 'Nice bag',
  document_type: ProductType.Product,
  image: null,
  completeness: 100,
  variant_product_completenesses: null,
};

const productModel = {
  id: 2,
  identifier: 'braided-hat',
  label: 'Braided hat',
  document_type: ProductType.ProductModel,
  image: {filePath: '/some.jpg', originalFileName: 'some'},
  completeness: null,
  variant_product_completenesses: {
    completeChildren: 1,
    totalChildren: 2,
  },
};

describe('row', () => {
  it('should add the provided products to the provided rows', () => {
    expect(addProductToRows([productRow, productModelRow], [product, productModel])).toEqual([
      {
        ...productRow,
        product,
      },
      {
        ...productModelRow,
        product: productModel,
      },
    ]);

    expect(addProductToRows([productRow, productModelRow], null)).toEqual([productRow, productModelRow]);
  });

  it('should return the identifiers of the provided rows', () => {
    expect(getAssociationIdentifiers([productRow, productModelRow])).toEqual({
      products: ['3fa79b52-5900-49e8-a197-1181f58ec3cb'],
      product_models: ['braided-hat'],
    });
  });

  it('can filter a row on its label or identifier or uuid', () => {
    expect(filterOnLabelOrIdentifier('b')(productRow)).toEqual(false);
    expect(filterOnLabelOrIdentifier('ba')(productRow)).toEqual(false);
    expect(filterOnLabelOrIdentifier('Nice')({...productRow, product})).toEqual(true);
    expect(filterOnLabelOrIdentifier('ba')({...productRow, product})).toEqual(true);
    expect(filterOnLabelOrIdentifier('3fa79b52-5900-49e8-a197-1181f58ec3cb')({...productRow, product})).toEqual(true);
    expect(filterOnLabelOrIdentifier('k')(productRow)).toEqual(false);
  });

  it('should set a row within a collection', () => {
    expect(
      updateRowInCollection([productRow, productModelRow], {
        ...productRow,
        quantifiedLink: {...productRow.quantifiedLink, quantity: 5},
      })
    ).toEqual([{...productRow, quantifiedLink: {...productRow.quantifiedLink, quantity: 5}}, productModelRow]);
  });

  it('should remove a row from a collection', () => {
    expect(removeRowFromCollection([productRow, productModelRow], productRow)).toEqual([productModelRow]);
  });

  it('should add rows in a collection', () => {
    expect(
      addRowsToCollection(
        [productRow],
        [
          {
            productType: ProductType.Product,
            quantifiedLink: {quantity: 87, identifier: 'sock'},
            product: null,
            errors: [],
          },
          {
            productType: ProductType.ProductModel,
            quantifiedLink: {quantity: 64, identifier: 'braided-sock'},
            product: null,
            errors: [],
          },
        ]
      )
    ).toEqual([
      productRow,
      {
        productType: ProductType.Product,
        quantifiedLink: {quantity: 87, identifier: 'sock'},
        product: null,
        errors: [],
      },
      {
        productType: ProductType.ProductModel,
        quantifiedLink: {quantity: 64, identifier: 'braided-sock'},
        product: null,
        errors: [],
      },
    ]);
  });
});
