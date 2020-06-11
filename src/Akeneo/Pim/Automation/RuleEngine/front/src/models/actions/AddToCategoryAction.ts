import { AddToCategoryActionLine } from '../../pages/EditRules/components/actions/AddToCategoryActionLine';
import { ActionModuleGuesser } from "./ActionModuleGuesser";
import { CategoryCode } from "../Category";

export type AddToCategoryAction = {
  type: 'add';
  field: 'categories';
  value: CategoryCode[];
};

export const getAddToCategoryModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }
  if (json.field !== 'categories') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddToCategoryActionLine);
};

export const createAddToCategoryAction: () => AddToCategoryAction = () => {
  return {
    type: 'add',
    field: 'categories',
    value: [],
  };
};
