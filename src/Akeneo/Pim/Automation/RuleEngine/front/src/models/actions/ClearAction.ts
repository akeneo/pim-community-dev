import { ClearActionLine } from '../../pages/EditRules/components/actions/ClearActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';

export type ClearAction = {
  type: 'clear';
} & ProductField;

export const getClearActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ClearActionLine);
};
