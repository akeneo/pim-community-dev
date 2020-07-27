import { GroupCode } from '../Group';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { AddGroupsActionLine } from '../../pages/EditRules/components/actions/AddGroupsActionLine';

export type AddGroupsAction = {
  type: 'add';
  field: 'groups';
  items: GroupCode[];
};

export const getAddGroupsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }
  if (json.field !== 'groups') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddGroupsActionLine);
};

export const createAddGroupsAction: () => AddGroupsAction = () => {
  return {
    type: 'add',
    field: 'groups',
    items: [],
  };
};
