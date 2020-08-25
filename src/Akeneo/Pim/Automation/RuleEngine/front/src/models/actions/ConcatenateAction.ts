import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { LocaleCode } from "../Locale";

export type ConcatenateSource = ProductField & {
  label_locale?: LocaleCode;
  format?: string;
}

export type ConcatenateAction = {
  type: 'concatenate';
  from: ConcatenateSource[];
  to: ProductField;
};

export const getConcatenateActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'concatenate') {
    return Promise.resolve(null);
  }

  return Promise.resolve(ConcatenateActionLine);
};

export const createConcatenateAction: () => ConcatenateAction = () => {
  return {
    type: 'concatenate',
    from: [],
    to: {
      field: '',
    },
  };
};

