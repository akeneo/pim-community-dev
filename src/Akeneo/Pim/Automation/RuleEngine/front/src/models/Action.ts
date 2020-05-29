import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  CalculateAction,
  ClearAction,
  ConcatenateAction,
  CopyAction,
  createSetFamilyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
} from './actions';

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_family: createSetFamilyAction,
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
