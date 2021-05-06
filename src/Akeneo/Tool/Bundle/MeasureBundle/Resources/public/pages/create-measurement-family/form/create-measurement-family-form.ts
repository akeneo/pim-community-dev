import {MeasurementFamily} from '../../../model/measurement-family';
import {Operator} from '../../../model/operation';
import {LocaleCode} from '../../../model/locale';

type CreateMeasurementFamilyForm = {
  family_code: string;
  family_label: string;
  standard_unit_code: string;
  standard_unit_label: string;
  standard_unit_symbol: string;
};

const initializeCreateMeasurementFamilyForm = () => {
  return Object.freeze({
    family_code: '',
    family_label: '',
    standard_unit_code: '',
    standard_unit_label: '',
    standard_unit_symbol: '',
  });
};

const createMeasurementFamilyFromForm = (data: CreateMeasurementFamilyForm, locale: LocaleCode): MeasurementFamily => {
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
    is_locked: false,
  };
};

export {initializeCreateMeasurementFamilyForm, createMeasurementFamilyFromForm};
export type {CreateMeasurementFamilyForm};
