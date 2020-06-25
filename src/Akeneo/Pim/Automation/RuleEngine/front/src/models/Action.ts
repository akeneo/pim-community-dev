import { FallbackAction } from './actions/FallbackAction';
import {
  AddAction,
  AddCategoriesAction,
  CalculateAction,
  ClearAction,
  ClearAttributeAction,
  ClearCategoriesAction,
  ClearGroupsAction,
  ConcatenateAction,
  CopyAction,
  createClearAttributeAction,
  createAddCategoriesAction,
  createSetAttributeAction,
  createSetFamilyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
  SetCategoriesAction,
  createSetCategoriesAction,
  createClearCategoriesAction,
  createClearGroupsAction,
>>>>>>> RUL-236 Add clear groups action line
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_category: createSetCategoriesAction,
  set_family: createSetFamilyAction,
  clear_attribute: createClearAttributeAction,
  clear_categories: createClearCategoriesAction,
  clear_groups: createClearGroupsAction,
  add_category: createAddCategoriesAction,
  set_attribute: createSetAttributeAction,
};

export type Action =
  | AddAction
  | AddCategoriesAction
  | CalculateAction
  | ClearAction
  | ClearAttributeAction
  | ClearCategoriesAction
  | ClearGroupsAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAction
  | SetAction
  | SetFamilyAction
  | SetCategoriesAction;
