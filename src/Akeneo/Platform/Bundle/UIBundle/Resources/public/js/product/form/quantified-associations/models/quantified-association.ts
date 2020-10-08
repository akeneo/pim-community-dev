import {Identifier, Row, ProductType, ProductsType, getProductsType} from '../models';
import {ValidationError, filterErrors} from '@akeneo-pim-community/shared';

const MAX_QUANTITY = 2147483647;

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

const quantifiedAssociationToRowCollection = (collection: QuantifiedAssociation, errors: ValidationError[]): Row[] => [
  ...collection.products.map((quantifiedLink, index) => ({
    quantifiedLink,
    productType: ProductType.Product,
    product: null,
    errors: filterErrors(errors, `.${ProductsType.Products}[${index}].`),
  })),
  ...collection.product_models.map((quantifiedLink, index) => ({
    quantifiedLink,
    productType: ProductType.ProductModel,
    product: null,
    errors: filterErrors(errors, `.${ProductsType.ProductModels}[${index}].`),
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
  const newAndUpdatedProductQuantifiedLinks = quantifiedAssociations.products.filter((quantifiedLink) => {
    const parentQuantifiedLink = parentQuantifiedAssociations.products.find(
      (parentQuantifiedLink) => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined === parentQuantifiedLink || parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  });

  const newAndUpdatedProductModelQuantifiedLinks = quantifiedAssociations.product_models.filter((quantifiedLink) => {
    const parentQuantifiedLink = parentQuantifiedAssociations.product_models.find(
      (parentQuantifiedLink) => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined === parentQuantifiedLink || parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  });

  return newAndUpdatedProductQuantifiedLinks.length + newAndUpdatedProductModelQuantifiedLinks.length;
};

const hasUpdatedQuantifiedAssociations = (
  parentQuantifiedAssociations: QuantifiedAssociation,
  quantifiedAssociations: QuantifiedAssociation
): boolean =>
  quantifiedAssociations.products.some((quantifiedLink) => {
    const parentQuantifiedLink = parentQuantifiedAssociations.products.find(
      (parentQuantifiedLink) => quantifiedLink.identifier === parentQuantifiedLink.identifier
    );

    return undefined !== parentQuantifiedLink && parentQuantifiedLink.quantity !== quantifiedLink.quantity;
  }) ||
  quantifiedAssociations.product_models.some((quantifiedLink) => {
    const parentQuantifiedLink = parentQuantifiedAssociations.product_models.find(
      (parentQuantifiedLink) => quantifiedLink.identifier === parentQuantifiedLink.identifier
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
  MAX_QUANTITY,
};
