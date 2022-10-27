import {Identifier, Row, ProductType, ProductsType} from '../models';
import {ValidationError, filterErrors} from '@akeneo-pim-community/shared';

const MAX_QUANTITY = 2147483647;

type ProductQuantifiedLink = {
  uuid: Identifier;
  quantity: number;
};

type ProductModelQuantifiedLink = {
  identifier: Identifier;
  quantity: number;
};

type QuantifiedLink = ProductQuantifiedLink | ProductModelQuantifiedLink;

type QuantifiedAssociation = {
  products: ProductQuantifiedLink[];
  product_models: ProductModelQuantifiedLink[];
};

const getQuantifiedLinkIdentifier = (quantifiedLink: QuantifiedLink) => {
  if (isProductQuantifiedLink(quantifiedLink)) {
    return quantifiedLink.uuid;
  }

  return quantifiedLink.identifier;
};

const isQuantifiedAssociationEmpty = (quantifiedAssociation: QuantifiedAssociation): boolean =>
  0 === quantifiedAssociation.products.length && 0 === quantifiedAssociation.product_models.length;

const isProductQuantifiedLink = (quantifiedLink: QuantifiedLink): quantifiedLink is ProductQuantifiedLink =>
  'object' === typeof quantifiedLink && 'uuid' in quantifiedLink && 'quantity' in quantifiedLink;

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

  rows.forEach(({quantifiedLink}) => {
    if (isProductQuantifiedLink(quantifiedLink)) {
      result.products.push({uuid: quantifiedLink.uuid, quantity: quantifiedLink.quantity});
    } else {
      result.product_models.push({identifier: quantifiedLink.identifier, quantity: quantifiedLink.quantity});
    }
  });

  return result;
};

const newAndUpdatedQuantifiedAssociationsCount = (
  parentQuantifiedAssociations: QuantifiedAssociation,
  quantifiedAssociations: QuantifiedAssociation
): number => {
  const newAndUpdatedProductQuantifiedLinks = quantifiedAssociations.products.filter(quantifiedLink => {
    const parentQuantifiedLink = parentQuantifiedAssociations.products.find(
      parentQuantifiedLink => quantifiedLink.uuid === parentQuantifiedLink.uuid
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
      parentQuantifiedLink => quantifiedLink.uuid === parentQuantifiedLink.uuid
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
  ProductModelQuantifiedLink,
  ProductQuantifiedLink,
  QuantifiedAssociation,
  getQuantifiedLinkIdentifier,
  quantifiedAssociationToRowCollection,
  rowCollectionToQuantifiedAssociation,
  newAndUpdatedQuantifiedAssociationsCount,
  hasUpdatedQuantifiedAssociations,
  isQuantifiedAssociationEmpty,
  MAX_QUANTITY,
};
