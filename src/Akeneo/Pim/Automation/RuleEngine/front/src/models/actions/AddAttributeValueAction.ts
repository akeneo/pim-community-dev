import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { AddAttributeValueActionLine } from '../../pages/EditRules/components/actions/AddAttributeValueActionLine';

export type AddAttributeValueAction = {
  type: 'add';
  items: string[];
} & ProductField;

export const getAddAttributeValueActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddAttributeValueActionLine);
};

export const createAddAttributeValueAction: () => AddAttributeValueAction = () => {
  return {
    type: 'add',
    field: '',
    items: [],
  };
};
