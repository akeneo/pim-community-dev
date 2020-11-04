import {SetActionLine} from '../../pages/EditRules/components/actions/SetActionLine';
import {ProductField} from './ProductField';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type SetAction = {
  type: 'set';
  value: any;
} & ProductField;

export const getSetActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'set') {
    return Promise.resolve(null);
  }

  return Promise.resolve(SetActionLine);
};

export const createSetAttributeAction = (): SetAction => ({
  type: 'set',
  value: null,
  field: '',
  locale: null,
  scope: null,
});
