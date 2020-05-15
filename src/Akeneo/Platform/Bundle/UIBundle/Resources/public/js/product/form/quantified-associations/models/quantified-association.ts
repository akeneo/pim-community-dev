import {QuantifiedLink, Identifier, AssociationIdentifiers, ProductsType} from '.';
import {Row} from '../components/QuantifiedAssociations';
import {ProductType} from './product';

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
  if (!(associationTypeCode in quantifiedAssociationCollection)) {
    return {products: [], product_models: []};
  }

  const productQuantifiedLinks = quantifiedAssociationCollection[associationTypeCode].products;
  const productIdentifiers =
    undefined === productQuantifiedLinks ? [] : productQuantifiedLinks.map(({identifier}) => identifier);
  const productModelQuantifiedLinks = quantifiedAssociationCollection[associationTypeCode].product_models;
  const productModelIdentifiers =
    undefined === productModelQuantifiedLinks ? [] : productModelQuantifiedLinks.map(({identifier}) => identifier);

  return {products: productIdentifiers, product_models: productModelIdentifiers};
};

const getQuantifiedLinkForIdentifier = (
  quantifiedAssociationCollection: QuantifiedAssociationCollection,
  associationTypeCode: string,
  productsType: ProductsType,
  identifier: Identifier
): QuantifiedLink | undefined => {
  const quantifiedLink = quantifiedAssociationCollection[associationTypeCode][productsType].find(
    entity => entity.identifier === identifier
  );

  // if (undefined === quantifiedLink) {
  //   throw Error('Quantified link not found');
  // }

  return quantifiedLink;
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
