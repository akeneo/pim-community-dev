import { RemoveActionLine } from '../../pages/EditRules/components/actions/RemoveActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';

export type RemoveAction = {
  type: 'remove';
  items: string[];
  include_children: boolean | null;
} & ProductField;

export const getRemoveActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'remove') {
    return Promise.resolve(null);
  }

  return Promise.resolve(RemoveActionLine);
};
