import {ProductType, QuantifiedLink, Product, ProductsType, getProductsType, AssociationIdentifiers} from '../models';
import {ValidationError} from '@akeneo-pim-community/shared';

type Row = {
  quantifiedLink: QuantifiedLink;
  productType: ProductType;
  product: null | Product;
  errors: ValidationError[];
};

const addProductToRows = (rows: Row[], products: Product[]): Row[] =>
  rows.map((row: Row) => {
    if (null === products) return {...row, product: null};
    const product = products.find((product) => product.identifier === row.quantifiedLink.identifier);
    if (undefined === product) return {...row, product: null};

    return {...row, product};
  });

const getAssociationIdentifiers = (rows: Row[]): AssociationIdentifiers =>
  rows.reduce(
    (identifiers: AssociationIdentifiers, row): AssociationIdentifiers => {
      identifiers[getProductsType(row.productType)].push(row.quantifiedLink.identifier);

      return identifiers;
    },
    {
      [ProductsType.Products]: [],
      [ProductsType.ProductModels]: [],
    }
  );

const filterOnLabelOrIdentifier = (searchValue: string) => (row: Row): boolean =>
  (null !== row.product &&
    null !== row.product.label &&
    -1 !== row.product.label.toLowerCase().indexOf(searchValue.toLowerCase())) ||
  (undefined !== row.quantifiedLink.identifier &&
    -1 !== row.quantifiedLink.identifier.toLowerCase().indexOf(searchValue.toLowerCase()));

const updateRowInCollection = (rows: Row[], {quantifiedLink, productType}: Row) =>
  rows.map((row) => {
    if (row.quantifiedLink.identifier !== quantifiedLink.identifier || row.productType !== productType) return row;

    return {...row, quantifiedLink};
  });

const removeRowFromCollection = (collection: Row[], {quantifiedLink, productType}: Row) =>
  collection.filter(
    (row) => row.quantifiedLink.identifier !== quantifiedLink.identifier || row.productType !== productType
  );

const addRowsToCollection = (collection: Row[], addedRows: Row[]) =>
  addedRows.reduce(
    (collection: Row[], addedRow: Row) => {
      const row = collection.find(
        (row) =>
          addedRow.quantifiedLink.identifier === row.quantifiedLink.identifier &&
          addedRow.productType === row.productType
      );

      if (undefined !== row) {
        row.quantifiedLink.quantity = 1;
      } else {
        collection.push(addedRow);
      }

      return collection;
    },
    [...collection]
  );

export {
  Row,
  filterOnLabelOrIdentifier,
  getAssociationIdentifiers,
  addProductToRows,
  updateRowInCollection,
  addRowsToCollection,
  removeRowFromCollection,
};
