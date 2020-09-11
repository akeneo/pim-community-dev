import { ActionModuleGuesser } from './ActionModuleGuesser';
import { ProductIdentifier, ProductModelCode } from '../';
import { SetQuantifiedAssociationsActionLine } from '../../pages/EditRules/components/actions/SetQuantifiedAssociationsActionLine';

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

export type SetQuantifiedAssociationsAction = {
  type: 'set';
  field: 'quantified_associations';
  value?: QuantifiedAssociationValue;
};

export const getSetQuantifiedAssociationsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'quantified_associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetQuantifiedAssociationsActionLine);
};

export const createSetQuantifiedAssociationsAction = (): SetQuantifiedAssociationsAction => ({
  type: 'set',
  field: 'quantified_associations',
});
