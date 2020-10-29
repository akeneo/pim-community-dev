import {ActionModuleGuesser} from './ActionModuleGuesser';
import {AddAssociationsActionLine} from '../../pages/EditRules/components/actions/AddAssociationsActionLine';
import {AssociationValue} from '../Association';

export type AddAssociationsAction = {
  type: 'add';
  field: 'associations';
  value?: AssociationValue;
};

export const getAddAssociationsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'add') {
    return Promise.resolve(null);
  }
  if (json.field !== 'associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(AddAssociationsActionLine);
};

export const createAddAssociationsAction = (): AddAssociationsAction => ({
  type: 'add',
  field: 'associations',
});
