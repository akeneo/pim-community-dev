import {GroupCode} from '../Group';
import {ActionModuleGuesser} from './ActionModuleGuesser';
import {SetGroupsActionLine} from '../../pages/EditRules/components/actions/SetGroupsActionLine';

export type SetGroupsAction = {
  type: 'set';
  field: 'groups';
  value: GroupCode[];
};

export const getSetGroupsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }

  if (json.field !== 'groups') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetGroupsActionLine);
};

export const createSetGroupsAction: () => SetGroupsAction = () => {
  return {
    type: 'set',
    field: 'groups',
    value: [],
  };
};
