import {SetFamilyActionLine} from '../../pages/EditRules/components/actions/SetFamilyActionLine';
import {FamilyCode} from '../Family';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type SetFamilyAction = {
  type: 'set';
  field: 'family';
  value: FamilyCode | null;
};

export const getSetFamilyActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'family') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetFamilyActionLine);
};

export const createSetFamilyAction: () => SetFamilyAction = () => {
  return {
    type: 'set',
    field: 'family',
    value: null,
  };
};
