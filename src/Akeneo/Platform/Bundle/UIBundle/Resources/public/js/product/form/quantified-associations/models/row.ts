import {ProductType, Identifier, Product} from '../models';

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

const isRowWithProduct = (row: Row): row is RowWithProduct => null !== row.product;

const filterOnLabelOrIdentifier = (searchValue: string) => (row: Row): boolean =>
  (null !== row.product && -1 !== row.product.label.toLowerCase().indexOf(searchValue.toLowerCase())) ||
  (undefined !== row.identifier && -1 !== row.identifier.toLowerCase().indexOf(searchValue.toLowerCase()));

export {Row, RowWithProduct, isRowWithProduct, filterOnLabelOrIdentifier};
