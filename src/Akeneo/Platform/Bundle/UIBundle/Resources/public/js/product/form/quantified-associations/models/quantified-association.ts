import {QuantifiedLink, Row, ProductType} from '../models';

type QuantifiedAssociation = {
  products: QuantifiedLink[];
  product_models: QuantifiedLink[];
};

type QuantifiedAssociationCollection = {
  [associationTypeCode: string]: QuantifiedAssociation;
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

export {QuantifiedAssociationCollection, setQuantifiedAssociationCollection};
