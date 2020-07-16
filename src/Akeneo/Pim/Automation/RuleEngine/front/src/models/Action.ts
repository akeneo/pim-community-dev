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
  createClearAssociationsAction,
  createAddCategoriesAction,
  createSetAttributeAction,
  createSetFamilyAction,
  createRemoveCategoriesAction,
  RemoveAttributeValueAction,
  SetAction,
  SetFamilyAction,
  SetCategoriesAction,
  createSetCategoriesAction,
  ClearAssociationsAction,
  createClearCategoriesAction,
  createClearGroupsAction,
  createCopyAction,
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_category: createSetCategoriesAction,
  set_family: createSetFamilyAction,
  clear_attribute: createClearAttributeAction,
  clear_associations: createClearAssociationsAction,
  clear_categories: createClearCategoriesAction,
  clear_groups: createClearGroupsAction,
  add_category: createAddCategoriesAction,
  set_attribute: createSetAttributeAction,
  copy: createCopyAction,
  remove_category: createRemoveCategoriesAction,
};

export type Action =
  | AddAction
  | AddCategoriesAction
  | CalculateAction
  | ClearAction
  | ClearAttributeAction
  | ClearAssociationsAction
  | ClearCategoriesAction
  | ClearGroupsAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAttributeValueAction
  | SetAction
  | SetFamilyAction
  | SetCategoriesAction;
