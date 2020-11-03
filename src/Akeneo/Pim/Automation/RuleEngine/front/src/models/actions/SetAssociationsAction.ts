import {ActionModuleGuesser} from './ActionModuleGuesser';
import {SetAssociationsActionLine} from '../../pages/EditRules/components/actions/SetAssociationsActionLine';
import {AssociationValue} from '../Association';

export type SetAssociationsAction = {
  type: 'set';
  field: 'associations';
  value?: AssociationValue;
};

export const getSetAssociationsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetAssociationsActionLine);
};

export const createSetAssociationsAction = (): SetAssociationsAction => ({
  type: 'set',
  field: 'associations',
});
