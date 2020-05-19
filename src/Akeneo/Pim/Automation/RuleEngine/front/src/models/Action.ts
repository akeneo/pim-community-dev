import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  ClearAction,
  ConcatenateAction,
  CopyAction,
  RemoveAction,
  SetAction,
} from './actions';

export type Action =
  | FallbackAction
  | ClearAction
  | CopyAction
  | AddAction
  | RemoveAction
  | SetAction
  | ConcatenateAction;
