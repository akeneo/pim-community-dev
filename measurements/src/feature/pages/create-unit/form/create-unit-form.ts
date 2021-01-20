import {Operation, Operator} from 'akeneomeasure/model/operation';
import {Unit} from 'akeneomeasure/model/unit';
import {LocaleCode} from 'akeneomeasure/model/locale';
import {MeasurementFamily} from 'akeneomeasure/model/measurement-family';
import {Translate} from '@akeneo-pim-community/legacy-bridge';
import {ValidationError} from '@akeneo-pim-community/shared';

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

const validateCreateUnitForm = (
  data: CreateUnitForm,
  measurementFamily: MeasurementFamily,
  __: Translate
): ValidationError[] => {
  const unitCodes = measurementFamily.units.map((unit: Unit) => unit.code.toLowerCase());

  if (unitCodes.includes(data.code.toLowerCase())) {
    return [
      {
        messageTemplate: 'measurements.validation.unit.code.must_be_unique',
        parameters: {},
        message: __('measurements.validation.unit.code.must_be_unique'),
        propertyPath: 'code',
        invalidValue: data.code,
      },
    ];
  }

  return [];
};

export {CreateUnitForm, initializeCreateUnitForm, createUnitFromForm, validateCreateUnitForm};
