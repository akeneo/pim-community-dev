import {ActionModuleGuesser} from './ActionModuleGuesser';
import {ClearGroupsActionLine} from '../../pages/EditRules/components/actions/ClearGroupsActionLine';

export type ClearGroupsAction = {
  type: 'clear';
  field: 'groups';
};

export const getClearGroupsActionModule: ActionModuleGuesser = async json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }

  if (json.field !== 'groups') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ClearGroupsActionLine);
};

export const createClearGroupsAction: () => ClearGroupsAction = () => {
  return {
    type: 'clear',
    field: 'groups',
  };
};
