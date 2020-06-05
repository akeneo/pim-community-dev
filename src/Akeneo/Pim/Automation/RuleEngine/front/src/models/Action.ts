import { FallbackAction } from './FallbackAction';
import {
  AddAction,
  CalculateAction,
  ClearAction,
  ClearAttributeAction,
  ConcatenateAction,
  CopyAction,
  createClearAttributeAction,
  createSetFamilyAction,
  RemoveAction,
  SetAction,
  SetFamilyAction,
} from './actions';
import { Router } from '../dependenciesTools';
import { ActionLineProps } from "../pages/EditRules/components/actions/ActionLineProps";

export const AvailableAddAction: { [key: string]: () => Action } = {
  set_family: createSetFamilyAction,
  clear_attribute: createClearAttributeAction,
};

export type ActionModuleGuesser = (json: any) => React.FC<ActionLineProps> | null;

export type Action =
  | AddAction
  | CalculateAction
  | ClearAction
  | ClearAttributeAction
  | ConcatenateAction
  | CopyAction
  | FallbackAction
  | RemoveAction
  | SetAction
  | SetFamilyAction;

export type ActionDenormalizer = (
  json: any,
  router: Router
) => Promise<Action | null>;
