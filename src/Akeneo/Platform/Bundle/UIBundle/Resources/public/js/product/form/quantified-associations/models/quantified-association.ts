import {QuantifiedLink, Identifier, AssociationIdentifiers, ProductsType, Row, ProductType} from '../models';

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: QuantifiedAssociation;
};

const getQuantifiedAssociationCollectionIdentifiers = (
  quantifiedAssociationCollection: Row[],
  associationTypeCode: string
): AssociationIdentifiers => {
  const productIdentifiers = quantifiedAssociationCollection
    .filter(row => ProductType.Product === row.productType && associationTypeCode === row.associationTypeCode)
    .map(({identifier}) => identifier);
  const productModelIdentifiers = quantifiedAssociationCollection
    .filter(row => ProductType.ProductModel === row.productType && associationTypeCode === row.associationTypeCode)
    .map(({identifier}) => identifier);

  return {products: productIdentifiers, product_models: productModelIdentifiers};
};

const getQuantifiedLinkForIdentifier = (
  quantifiedAssociationCollection: QuantifiedAssociationCollection,
  associationTypeCode: string,
  productsType: ProductsType,
  identifier: Identifier
): QuantifiedLink | undefined => {
  return quantifiedAssociationCollection[associationTypeCode][productsType].find(
    entity => entity.identifier === identifier
  );
};

const setQuantifiedAssociationCollection = (
  rows: Row[],
  associationTypeCode: string,
  productType: ProductType,
  {identifier, quantity}: QuantifiedLink
) => {
  return rows.map(row => {
    if (
      row.identifier !== identifier ||
      row.productType !== productType ||
      row.associationTypeCode !== associationTypeCode
    )
      return row;

    return {...row, quantity};
  });
};

export {
  QuantifiedAssociationCollection,
  getQuantifiedAssociationCollectionIdentifiers,
  getQuantifiedLinkForIdentifier,
  setQuantifiedAssociationCollection,
};
