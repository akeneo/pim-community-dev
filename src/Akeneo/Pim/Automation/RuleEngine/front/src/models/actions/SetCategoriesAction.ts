import {SetCategoriesActionLine} from '../../pages/EditRules/components/actions/SetCategoriesActionLine';
import {ActionModuleGuesser} from './ActionModuleGuesser';
import {CategoryCode} from '../Category';

export type SetCategoriesAction = {
  type: 'set';
  field: 'categories';
  value: CategoryCode[];
};

export const getSetCategoriesModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetCategoriesActionLine);
};

export const createSetCategoriesAction: () => SetCategoriesAction = () => {
  return {
    type: 'set',
    field: 'categories',
    value: [],
  };
};
