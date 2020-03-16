import {MeasurementFamily, Operator} from 'akeneomeasure/model/measurement-family';

type LocaleCode = string;

type FormState = {
  family_code: string;
  family_label: string;
  standard_unit_code: string;
  standard_unit_label: string;
  standard_unit_symbol: string;
};

const createFormState = () => {
  return Object.freeze({
    family_code: '',
    family_label: '',
    standard_unit_code: '',
    standard_unit_label: '',
    standard_unit_symbol: '',
  });
};

const createMeasurementFamilyFromFormState = (data: FormState, locale: LocaleCode): MeasurementFamily => {
  return {
    code: data.family_code,
    labels: {
      [locale]: data.family_label,
    },
    standard_unit_code: data.standard_unit_code,
    units: [
      {
        code: data.standard_unit_code,
        labels: {
          [locale]: data.standard_unit_label,
        },
        symbol: data.standard_unit_symbol,
        convert_from_standard: [
          {
            operator: Operator.MUL,
            value: '1',
          },
        ],
      },
    ],
  };
};

export {FormState, createFormState, createMeasurementFamilyFromFormState};
