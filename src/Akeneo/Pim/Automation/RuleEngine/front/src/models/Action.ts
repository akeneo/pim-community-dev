import { FallbackAction } from './FallbackAction';
import {
  ClearAction,
  CopyAction,
  AddAction,
  RemoveAction,
  ConcatenateAction,
} from './actions';

export type Action =
  | FallbackAction
  | ClearAction
  | CopyAction
  | AddAction
  | RemoveAction
  | ConcatenateAction;
