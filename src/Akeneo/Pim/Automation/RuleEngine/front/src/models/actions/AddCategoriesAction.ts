import {AddCategoriesActionLine} from '../../pages/EditRules/components/actions/AddCategoriesActionLine';
import {ActionModuleGuesser} from './ActionModuleGuesser';
import {CategoryCode} from '../Category';

export type AddCategoriesAction = {
  type: 'add';
  field: 'categories';
  items: CategoryCode[];
};

export const getAddCategoriesModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddCategoriesActionLine);
};

export const createAddCategoriesAction: () => AddCategoriesAction = () => {
  return {
    type: 'add',
    field: 'categories',
    items: [],
  };
};
