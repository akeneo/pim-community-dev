import {ActionModuleGuesser} from './ActionModuleGuesser';
import {SetStatusActionLine} from '../../pages/EditRules/components/actions/SetStatusActionLine';

export type SetStatusAction = {
  type: 'set';
  field: 'enabled';
  value: boolean | null;
};

export const getSetStatusActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }
  if (json.field !== 'enabled') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetStatusActionLine);
};

export const createSetStatusAction: () => SetStatusAction = () => {
  return {
    type: 'set',
    field: 'enabled',
    value: null,
  };
};
