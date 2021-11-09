import {ActionModuleGuesser} from './ActionModuleGuesser';
import {ClearAssociationsActionLine} from '../../pages/EditRules/components/actions/ClearAssociationsActionLine';

export type ClearAssociationsAction = {
  type: 'clear';
  field: 'associations';
};

export const getClearAssociationsActionModule: ActionModuleGuesser = async json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }

  if (json.field !== 'associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ClearAssociationsActionLine);
};

export const createClearAssociationsAction: () => ClearAssociationsAction = () => {
  return {
    type: 'clear',
    field: 'associations',
  };
};
