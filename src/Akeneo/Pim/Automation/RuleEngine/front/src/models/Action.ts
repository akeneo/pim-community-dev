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
  | AddAction
  | CalculateAction
  | ClearAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAction
  | SetAction;
