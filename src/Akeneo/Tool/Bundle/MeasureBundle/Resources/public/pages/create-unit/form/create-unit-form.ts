import {Operation, Operator} from 'akeneomeasure/model/operation';
import {Unit} from 'akeneomeasure/model/unit';
import {LocaleCode} from 'akeneomeasure/model/locale';

type CreateUnitForm = {
  code: string;
  label: string;
  symbol: string;
  operations: Operation[];
};

const initializeCreateUnitForm = () => {
  return Object.freeze({
    code: '',
    label: '',
    symbol: '',
    operations: [
      {
        operator: Operator.MUL,
        value: '',
      },
    ],
  });
};

const createUnitFromForm = (data: CreateUnitForm, locale: LocaleCode): Unit => {
  return {
    code: data.code,
    labels: {
      [locale]: data.label,
    },
    symbol: data.symbol,
    convert_from_standard: data.operations,
  };
};

export {CreateUnitForm, initializeCreateUnitForm, createUnitFromForm};
