import { ActionModuleGuesser } from './ActionModuleGuesser';
import { ClearQuantifiedAssociationsActionLine } from "../../pages/EditRules/components/actions/ClearQuantifiedAssociationsActionLine";

export type ClearQuantifiedAssociationsAction = {
  type: 'clear';
  field: 'quantified_associations';
};

export const getClearQuantifiedAssociationsActionModule: ActionModuleGuesser = async json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }

  if (json.field !== 'quantified_associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ClearQuantifiedAssociationsActionLine);
};

export const createClearQuantifiedAssociationsAction: () => ClearQuantifiedAssociationsAction = () => {
  return {
    type: 'clear',
    field: 'quantified_associations',
  };
};
