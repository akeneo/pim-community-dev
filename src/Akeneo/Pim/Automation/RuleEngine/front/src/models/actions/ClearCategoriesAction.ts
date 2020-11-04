import {ActionModuleGuesser} from './ActionModuleGuesser';
import {ClearCategoriesActionLine} from '../../pages/EditRules/components/actions/ClearCategoriesActionLine';

export type ClearCategoriesAction = {
  type: 'clear';
  field: 'categories';
};

export const getClearCategoriesActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }
  return Promise.resolve(ClearCategoriesActionLine);
};

export const createClearCategoriesAction: () => ClearCategoriesAction = () => {
  return {
    type: 'clear',
    field: 'categories',
  };
};
