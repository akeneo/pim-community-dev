import {ProductField} from './ProductField';
import {ClearAttributeActionLine} from '../../pages/EditRules/components/actions/ClearAttributeActionLine';
import {ActionModuleGuesser} from './ActionModuleGuesser';

export type ClearAttributeAction = {
  type: 'clear';
} & ProductField;

export const getClearAttributeActionModule: ActionModuleGuesser = async json => {
  if (json.type !== 'clear') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ClearAttributeActionLine);
};

export const createClearAttributeAction: () => ClearAttributeAction = () => {
  return {
    type: 'clear',
    field: '',
    locale: null,
    scope: null,
  };
};
