import { FallbackAction } from './actions/FallbackAction';
import {
  AddAction,
  AddAttributeValueAction,
  AddCategoriesAction,
  AddGroupsAction,
  CalculateAction,
  ClearAction,
  ClearAssociationsAction,
  ClearAttributeAction,
  ClearCategoriesAction,
  ClearGroupsAction,
  ConcatenateAction,
  CopyAction,
  RemoveAttributeValueAction,
  RemoveGroupsAction,
  SetAction,
  SetCategoriesAction,
  SetFamilyAction,
  SetStatusAction,
  createAddAttributeValueAction,
  createAddCategoriesAction,
  createAddGroupsAction,
  createClearAssociationsAction,
  createClearAttributeAction,
  createClearCategoriesAction,
  createClearGroupsAction,
  createCopyAction,
  createRemoveAttributeValueAction,
  createRemoveCategoriesAction,
  createRemoveGroupsAction,
  createSetAttributeAction,
  createSetCategoriesAction,
  createSetFamilyAction,
  createSetStatusAction,
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_attribute: createSetAttributeAction,
  set_category: createSetCategoriesAction,
  set_family: createSetFamilyAction,
  set_status: createSetStatusAction,
  clear_attribute: createClearAttributeAction,
  clear_associations: createClearAssociationsAction,
  clear_categories: createClearCategoriesAction,
  clear_groups: createClearGroupsAction,
  add_attribute_value: createAddAttributeValueAction,
  add_category: createAddCategoriesAction,
  add_groups: createAddGroupsAction,
  copy: createCopyAction,
  remove_attribute_value: createRemoveAttributeValueAction,
  remove_category: createRemoveCategoriesAction,
  remove_groups: createRemoveGroupsAction,
};

export type Action =
  | AddAction
  | AddAttributeValueAction
  | AddCategoriesAction
  | AddGroupsAction
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
  | RemoveGroupsAction
  | SetAction
  | SetFamilyAction
  | SetCategoriesAction
  | SetStatusAction;
