import {Operator, Unit} from 'akeneomeasure/model/measurement-family';

type LocaleCode = string;

type CreateUnitForm = {
  code: string;
  label: string;
  symbol: string;
};

const initializeCreateUnitForm = () => {
  return Object.freeze({
    code: '',
    label: '',
    symbol: '',
  });
};

const createUnitFromForm = (data: CreateUnitForm, locale: LocaleCode): Unit => {
  return {
    code: data.code,
    labels: {
      [locale]: data.label,
    },
    symbol: data.symbol,
    // @todo
    convert_from_standard: [
      {
        operator: Operator.MUL,
        value: '1',
      },
    ],
  };
};

export {CreateUnitForm, initializeCreateUnitForm, createUnitFromForm};
