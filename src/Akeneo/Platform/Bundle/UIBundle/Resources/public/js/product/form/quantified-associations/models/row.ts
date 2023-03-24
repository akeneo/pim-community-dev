import {
  AssociationIdentifiers,
  getProductsType,
  getQuantifiedLinkIdentifier,
  Product,
  ProductsType,
  ProductType,
  QuantifiedLink,
} from '../models';
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

    const product = products.find(product => {
      const quantifiedLinkIdentifier = product.document_type === 'product' ? product.id : product.identifier;

      return quantifiedLinkIdentifier === getQuantifiedLinkIdentifier(row.quantifiedLink);
    });
    if (undefined === product) return {...row, product: null};

    return {...row, product};
  });

const getAssociationIdentifiers = (rows: Row[]): AssociationIdentifiers =>
  rows.reduce(
    (identifiers: AssociationIdentifiers, row): AssociationIdentifiers => {
      identifiers[getProductsType(row.productType)].push(getQuantifiedLinkIdentifier(row.quantifiedLink));

      return identifiers;
    },
    {
      [ProductsType.Products]: [],
      [ProductsType.ProductModels]: [],
    }
  );

const filterOnLabelOrIdentifier =
  (searchValue: string) =>
  (row: Row): boolean => {
    if (null === row.product) {
      return false;
    }

    return (
      (null !== row.product.label && -1 !== row.product.label.toLowerCase().indexOf(searchValue.toLowerCase())) ||
      (null !== row.product.identifier &&
        -1 !== row.product.identifier.toLowerCase().indexOf(searchValue.toLowerCase())) ||
      (null !== row.product.id &&
        ProductType.Product === row.product.document_type &&
        row.product.id.toString().toLowerCase() === searchValue.toLowerCase())
    );
  };

const updateRowInCollection = (rows: Row[], {quantifiedLink, productType}: Row) =>
  rows.map(row => {
    if (
      getQuantifiedLinkIdentifier(row.quantifiedLink) !== getQuantifiedLinkIdentifier(quantifiedLink) ||
      row.productType !== productType
    )
      return row;

    return {...row, quantifiedLink};
  });

const removeRowFromCollection = (collection: Row[], {quantifiedLink, productType}: Row) =>
  collection.filter(
    row =>
      getQuantifiedLinkIdentifier(row.quantifiedLink) !== getQuantifiedLinkIdentifier(quantifiedLink) ||
      row.productType !== productType
  );

const addRowsToCollection = (collection: Row[], addedRows: Row[]) =>
  addedRows.reduce(
    (collection: Row[], addedRow: Row) => {
      const row = collection.find(
        row =>
          getQuantifiedLinkIdentifier(addedRow.quantifiedLink) === getQuantifiedLinkIdentifier(row.quantifiedLink) &&
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
