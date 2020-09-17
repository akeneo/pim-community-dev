import { ProductIdentifier } from './Product';
import { ProductModelCode } from './ProductModel';
import { GroupCode } from './Group';

export type AssociationValue = {
  [associationTypeCode: string]: {
    products?: ProductIdentifier[];
    product_models?: ProductModelCode[];
    groups?: GroupCode[];
  };
};

export type QuantifiedAssociationValue = {
  [associationTypeCode: string]: {
    products?: {
      identifier: ProductIdentifier;
      quantity: number;
    }[];
    product_models?: {
      identifier: ProductModelCode;
      quantity: number;
    }[];
  };
};
