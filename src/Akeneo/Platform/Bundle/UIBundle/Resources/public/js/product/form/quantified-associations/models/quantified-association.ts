import {Identifier, Row, ProductType, getProductsType} from '../models';

type QuantifiedLink = {
  identifier: Identifier;
  quantity: number;
};

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

const quantifiedAssociationToRowCollection = (collection: QuantifiedAssociation): Row[] => {
  const products = collection.products || [];
  const productModels = collection.product_models || [];

  return [
    ...products.map(quantifiedLink => ({
      quantifiedLink,
      productType: ProductType.Product,
      product: null,
    })),
    ...productModels.map(quantifiedLink => ({
      quantifiedLink,
      productType: ProductType.ProductModel,
      product: null,
    })),
  ];
};

const rowCollectionToQuantifiedAssociation = (rows: Row[]): QuantifiedAssociation => {
  const result: QuantifiedAssociation = {
    products: [],
    product_models: [],
  };

  rows.forEach(({quantifiedLink: {identifier, quantity}, productType}) =>
    result[getProductsType(productType)].push({identifier, quantity})
  );

  return result;
};

export {
  QuantifiedLink,
  QuantifiedAssociation,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
};
