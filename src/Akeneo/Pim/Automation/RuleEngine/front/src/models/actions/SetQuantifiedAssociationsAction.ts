import {ActionModuleGuesser} from './ActionModuleGuesser';
import {SetQuantifiedAssociationsActionLine} from '../../pages/EditRules/components/actions/SetQuantifiedAssociationsActionLine';
import {QuantifiedAssociationValue} from '../Association';

export type SetQuantifiedAssociationsAction = {
  type: 'set';
  field: 'quantified_associations';
  value: QuantifiedAssociationValue;
};

export const getSetQuantifiedAssociationsActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'quantified_associations') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetQuantifiedAssociationsActionLine);
};

export const createSetQuantifiedAssociationsAction = (): SetQuantifiedAssociationsAction => ({
  type: 'set',
  field: 'quantified_associations',
  value: {},
});
