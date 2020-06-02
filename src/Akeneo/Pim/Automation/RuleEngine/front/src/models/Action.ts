import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  CalculateAction,
  ClearAction,
  ConcatenateAction,
  CopyAction,
  createClearAction,
  createSetFamilyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_family: createSetFamilyAction,
  clear_attribute: createClearAction,
};

export type Action =
  | AddAction
  | CalculateAction
  | ClearAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAction
  | SetAction
  | SetFamilyAction;
