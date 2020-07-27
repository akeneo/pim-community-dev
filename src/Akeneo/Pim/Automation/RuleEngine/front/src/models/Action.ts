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
  createAddAttributeValueCategoriesAction,
  createAddCategoriesAction,
  createAddGroupsAction,
  createClearAssociationsAction,
  createClearAttributeAction,
  createClearCategoriesAction,
  createClearGroupsAction,
  createCopyAction,
  createRemoveAttributeValueCategoriesAction,
  createRemoveCategoriesAction,
  createSetAttributeAction,
  createSetCategoriesAction,
  createSetFamilyAction,
  createSetStatusAction,
  RemoveAttributeValueAction,
  SetAction,
  SetCategoriesAction,
  SetFamilyAction,
  SetStatusAction,
} from './actions';
import {
  createRemoveGroupsAction,
  RemoveGroupsAction,
} from './actions/RemoveGroupsAction';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_category: createSetCategoriesAction,
  set_family: createSetFamilyAction,
  set_status: createSetStatusAction,
  clear_attribute: createClearAttributeAction,
  clear_associations: createClearAssociationsAction,
  clear_categories: createClearCategoriesAction,
  clear_groups: createClearGroupsAction,
  add_attribute_value: createAddAttributeValueCategoriesAction,
  add_category: createAddCategoriesAction,
  add_groups: createAddGroupsAction,
  set_attribute: createSetAttributeAction,
  copy: createCopyAction,
  remove_category: createRemoveCategoriesAction,
  remove_attribute_value: createRemoveAttributeValueCategoriesAction,
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
