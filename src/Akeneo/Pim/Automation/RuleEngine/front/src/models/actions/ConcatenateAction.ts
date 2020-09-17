import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { LocaleCode } from '../Locale';

export type ConcatenateSource = {
  field?: string | null;
  locale?: string | null;
  scope?: string | null;
  label_locale?: LocaleCode;
  format?: string;
  text?: string | null;
  new_line?: null;
};

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
