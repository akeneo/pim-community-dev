import {QuantifiedLink, Identifier, AssociationIdentifiers, setQuantifiedLink, ProductsType} from '.';

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: QuantifiedAssociation;
};

const getQuantifiedAssociationCollectionIdentifiers = (
  quantifiedAssociationCollection: QuantifiedAssociationCollection,
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
): QuantifiedLink => {
  const quantifiedLink = quantifiedAssociationCollection[associationTypeCode][productsType].find(
    entity => entity.identifier === identifier
  );

  if (undefined === quantifiedLink) {
    throw Error('Quantified link not found');
  }

  return quantifiedLink;
};

const setQuantifiedAssociation = (
  quantifiedAssociation: QuantifiedAssociation,
  productsType: ProductsType,
  quantifiedLink: QuantifiedLink
): QuantifiedAssociation => ({
  ...quantifiedAssociation,
  [productsType]: setQuantifiedLink(quantifiedAssociation[productsType], quantifiedLink),
});

const setQuantifiedAssociationCollection = (
  quantifiedAssociationCollection: QuantifiedAssociationCollection,
  associationTypeCode: string,
  productsType: ProductsType,
  quantifiedLink: QuantifiedLink
): QuantifiedAssociationCollection =>
  Object.keys(quantifiedAssociationCollection).reduce(
    (updated, currentAssociationTypeCode) => ({
      ...updated,
      [currentAssociationTypeCode]:
        currentAssociationTypeCode === associationTypeCode
          ? setQuantifiedAssociation(
              quantifiedAssociationCollection[currentAssociationTypeCode],
              productsType,
              quantifiedLink
            )
          : quantifiedAssociationCollection[currentAssociationTypeCode],
    }),
    {}
  );

export {
  QuantifiedAssociationCollection,
  getQuantifiedAssociationCollectionIdentifiers,
  getQuantifiedLinkForIdentifier,
  setQuantifiedAssociationCollection,
};
