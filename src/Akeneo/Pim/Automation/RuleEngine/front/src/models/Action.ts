import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  CalculateAction,
  ClearAction,
  ConcatenateAction,
  CopyAction,
  RemoveAction,
  SetAction,
} from './actions';

export type Action =
  | FallbackAction
  | CalculateAction
  | ClearAction
  | CopyAction
  | AddAction
  | RemoveAction
  | SetAction
  | ConcatenateAction;
