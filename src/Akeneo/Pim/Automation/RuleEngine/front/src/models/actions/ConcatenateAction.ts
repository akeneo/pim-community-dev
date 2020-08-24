import { ConcatenateActionLine } from '../../pages/EditRules/components/actions/ConcatenateActionLine';
import { ProductField } from './ProductField';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { CurrencyCode } from "../Currency";
import { LocaleCode } from "../Locale";
import { MeasurementUnitCode } from "../MeasurementFamily";

export type ConcatenateSource = ProductField & {
  labelLocale?: LocaleCode;
  format?: string;
  currency?: CurrencyCode;
}

type ConcatenateTarget = ProductField & {
  currency?: CurrencyCode;
  unit?: MeasurementUnitCode;
}

export type ConcatenateAction = {
  type: 'concatenate';
  from: ConcatenateSource[];
  to: ConcatenateTarget;
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

