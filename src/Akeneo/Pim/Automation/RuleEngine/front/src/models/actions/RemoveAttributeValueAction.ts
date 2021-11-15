import {RemoveAttributeValueActionLine} from '../../pages/EditRules/components/actions/RemoveAttributeValueActionLine';
import {ProductField} from './ProductField';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type RemoveAttributeValueAction = {
  type: 'remove';
  items: string[];
} & ProductField;

export const getRemoveAttributeValueActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'remove') {
    return Promise.resolve(null);
  }

  return Promise.resolve(RemoveAttributeValueActionLine);
};

export const createRemoveAttributeValueAction: () => RemoveAttributeValueAction = () => {
  return {
    type: 'remove',
    field: '',
    items: [],
  };
};
