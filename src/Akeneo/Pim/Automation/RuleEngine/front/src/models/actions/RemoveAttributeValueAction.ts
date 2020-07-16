import { RemoveAttributeValueActionLine } from '../../pages/EditRules/components/actions/RemoveAttributeValueActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';

export type RemoveAttributeValueAction = {
  type: 'remove';
  items: string[];
  include_children: boolean | null;
} & ProductField;

export const getRemoveActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'remove') {
    return Promise.resolve(null);
  }

  return Promise.resolve(RemoveAttributeValueActionLine);
};
