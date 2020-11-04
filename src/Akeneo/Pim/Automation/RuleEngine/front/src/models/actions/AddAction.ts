import {AddActionLine} from '../../pages/EditRules/components/actions/AddActionLine';
import {ProductField} from './ProductField';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type AddAction = {
  type: 'add';
  items: string[];
} & ProductField;

export const getAddActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddActionLine);
};
