import { FallbackAction } from './actions/FallbackAction';
import {
  AddAction,
  AddCategoriesAction,
  AddGroupsAction,
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
  createRemoveAttributeValueCategoriesAction,
  createAddGroupsAction,
  createAddAttributeValueCategoriesAction,
  AddAttributeValueAction,
  createSetStatusAction,
  SetStatusAction,
} from './actions';

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
  | SetAction
  | SetFamilyAction
  | SetCategoriesAction
  | SetStatusAction;
