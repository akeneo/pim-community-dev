import {GroupCode} from '../Group';
import {ActionModuleGuesser} from './ActionModuleGuesser';
import {RemoveGroupsActionLine} from '../../pages/EditRules/components/actions/RemoveGroupsActionLine';

export type RemoveGroupsAction = {
  type: 'remove';
  field: 'groups';
  items: GroupCode[];
};

export const getRemoveGroupsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'remove') {
    return Promise.resolve(null);
  }
  if (json.field !== 'groups') {
    return Promise.resolve(null);
  }

  return Promise.resolve(RemoveGroupsActionLine);
};

export const createRemoveGroupsAction: () => RemoveGroupsAction = () => {
  return {
    type: 'remove',
    field: 'groups',
    items: [],
  };
};
