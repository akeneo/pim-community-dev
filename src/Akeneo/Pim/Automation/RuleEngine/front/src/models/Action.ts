import {FallbackAction} from './actions/FallbackAction';
import {
  AddAction,
  AddAttributeValueAction,
  AddCategoriesAction,
  AddGroupsAction,
  CalculateAction,
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
  SetGroupsAction,
  SetStatusAction,
  createAddAssociationsAction,
  createAddAttributeValueAction,
  createAddCategoriesAction,
  createAddGroupsAction,
  createClearAssociationsAction,
  createClearAttributeAction,
  createClearCategoriesAction,
  createClearGroupsAction,
  createClearQuantifiedAssociationsAction,
  createCopyAction,
  createRemoveAttributeValueAction,
  createRemoveCategoriesAction,
  createRemoveGroupsAction,
  createSetAttributeAction,
  createSetCategoriesAction,
  createSetFamilyAction,
  createSetGroupsAction,
  createSetStatusAction,
  createCalculateAction,
  createConcatenateAction,
  createSetAssociationsAction,
  createSetQuantifiedAssociationsAction,
} from './actions';

export const AvailableAddAction: {[key: string]: () => Action} = {
  set_associations: createSetAssociationsAction,
  set_attribute: createSetAttributeAction,
  set_category: createSetCategoriesAction,
  set_family: createSetFamilyAction,
  set_groups: createSetGroupsAction,
  set_quantified_associations: createSetQuantifiedAssociationsAction,
  set_status: createSetStatusAction,
  clear_attribute: createClearAttributeAction,
  clear_associations: createClearAssociationsAction,
  clear_categories: createClearCategoriesAction,
  clear_groups: createClearGroupsAction,
  clear_quantified_associations: createClearQuantifiedAssociationsAction,
  add_associations: createAddAssociationsAction,
  add_attribute_value: createAddAttributeValueAction,
  add_category: createAddCategoriesAction,
  add_groups: createAddGroupsAction,
  copy: createCopyAction,
  remove_attribute_value: createRemoveAttributeValueAction,
  remove_category: createRemoveCategoriesAction,
  remove_groups: createRemoveGroupsAction,
  calculate: createCalculateAction,
  concatenate: createConcatenateAction,
};

export type Action =
  | AddAction
  | AddAttributeValueAction
  | AddCategoriesAction
  | AddGroupsAction
  | CalculateAction
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
  | SetGroupsAction
  | SetCategoriesAction
  | SetStatusAction;
