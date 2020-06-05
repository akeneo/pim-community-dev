import { FallbackAction } from './actions/FallbackAction';
import {
  AddAction,
  AddToCategoryAction,
  CalculateAction,
  ClearAction,
  ClearAttributeAction,
  ConcatenateAction,
  CopyAction,
  createClearAttributeAction,
  createAddToCategoryAction,
  createSetFamilyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_family: createSetFamilyAction,
  clear_attribute: createClearAttributeAction,
  set_categories: createAddToCategoryAction,
};

export type Action =
  | AddAction
  | AddToCategoryAction
  | CalculateAction
  | ClearAction
  | ClearAttributeAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAction
  | SetAction
  | SetFamilyAction;
