import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  CalculateAction,
  ClearAction,
  ConcatenateAction,
  CopyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
} from './actions';

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
