import {ProductType, Identifier, Product, ProductsType, getProductsType, AssociationIdentifiers} from '../models';

type Row = {
  associationTypeCode: string;
  quantity: number;
  identifier: Identifier;
  productType: ProductType;
  product: null | Product;
};

type RowWithProduct = Row & {
  product: Product;
};

const addProductToRows = (rows: Row[], products: Product[]): Row[] =>
  rows.map((row: Row) => {
    if (null === products) return {...row, product: null};
    const product = products.find(product => product.identifier === row.identifier);
    if (undefined === product) return {...row, product: null};

    return {...row, product};
  });

const getAssociationIdentifiers = (rows: Row[]): AssociationIdentifiers =>
  rows.reduce(
    (identifiers: AssociationIdentifiers, row): AssociationIdentifiers => {
      identifiers[getProductsType(row.productType)].push(row.identifier);

      return identifiers;
    },
    {
      [ProductsType.Products]: [],
      [ProductsType.ProductModels]: [],
    }
  );

const isRowWithProduct = (row: Row): row is RowWithProduct => null !== row.product;

const filterOnLabelOrIdentifier = (searchValue: string) => (row: Row): boolean =>
  (null !== row.product &&
    null !== row.product.label &&
    -1 !== row.product.label.toLowerCase().indexOf(searchValue.toLowerCase())) ||
  (undefined !== row.identifier && -1 !== row.identifier.toLowerCase().indexOf(searchValue.toLowerCase()));

export {Row, RowWithProduct, isRowWithProduct, filterOnLabelOrIdentifier, getAssociationIdentifiers, addProductToRows};
