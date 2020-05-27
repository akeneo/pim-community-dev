import {Identifier, Row, ProductType, getProductsType} from '../models';

type QuantifiedLink = {
  identifier: Identifier;
  quantity: number;
};

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

const isQuantifiedAssociationEmpty = (quantifiedAssociation: QuantifiedAssociation): boolean =>
  0 === quantifiedAssociation.products.length && 0 === quantifiedAssociation.product_models.length;

const quantifiedAssociationToRowCollection = (collection: QuantifiedAssociation): Row[] => [
  ...collection.products.map(quantifiedLink => ({
    quantifiedLink,
    productType: ProductType.Product,
    product: null,
  })),
  ...collection.product_models.map(quantifiedLink => ({
    quantifiedLink,
    productType: ProductType.ProductModel,
    product: null,
  })),
];

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

const newAndUpdatedQuantifiedAssociationsCount = (
  parentQuantifiedAssociations: QuantifiedAssociation,
  quantifiedAssociations: QuantifiedAssociation
): number => {
  const newAndUpdatedProductQuantifiedLinks = quantifiedAssociations.products.filter(quantifiedLink => {
    const parentQuantifiedLink = parentQuantifiedAssociations.products.find(
      parentQuantifiedLink => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined === parentQuantifiedLink || parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  });

  const newAndUpdatedProductModelQuantifiedLinks = quantifiedAssociations.product_models.filter(quantifiedLink => {
    const parentQuantifiedLink = parentQuantifiedAssociations.product_models.find(
      parentQuantifiedLink => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined === parentQuantifiedLink || parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  });

  return newAndUpdatedProductQuantifiedLinks.length + newAndUpdatedProductModelQuantifiedLinks.length;
};

const hasUpdatedQuantifiedAssociations = (
  parentQuantifiedAssociations: QuantifiedAssociation,
  quantifiedAssociations: QuantifiedAssociation
): boolean =>
  quantifiedAssociations.products.some(quantifiedLink => {
    const parentQuantifiedLink = parentQuantifiedAssociations.products.find(
      parentQuantifiedLink => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined !== parentQuantifiedLink && parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  }) ||
  quantifiedAssociations.product_models.some(quantifiedLink => {
    const parentQuantifiedLink = parentQuantifiedAssociations.product_models.find(
      parentQuantifiedLink => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined !== parentQuantifiedLink && parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  });

export {
  QuantifiedLink,
  QuantifiedAssociation,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  newAndUpdatedQuantifiedAssociationsCount,
  hasUpdatedQuantifiedAssociations,
  isQuantifiedAssociationEmpty,
};
