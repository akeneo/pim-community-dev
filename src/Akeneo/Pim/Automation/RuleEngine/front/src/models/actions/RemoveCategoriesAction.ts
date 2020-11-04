import {RemoveCategoriesActionLine} from '../../pages/EditRules/components/actions/RemoveCategoriesActionLine';
import {ActionModuleGuesser} from './ActionModuleGuesser';
import {CategoryCode} from '../Category';

export type RemoveCategoriesAction = {
  type: 'remove';
  field: 'categories';
  items: CategoryCode[];
};

export const getRemoveCategoriesModule: ActionModuleGuesser = json => {
  if (json.type !== 'remove') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }

  return Promise.resolve(RemoveCategoriesActionLine);
};

export const createRemoveCategoriesAction: () => RemoveCategoriesAction = () => {
  return {
    type: 'remove',
    field: 'categories',
    items: [],
  };
};
