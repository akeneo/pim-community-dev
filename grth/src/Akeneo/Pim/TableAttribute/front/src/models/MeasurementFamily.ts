import {LabelCollection} from '@akeneo-pim-community/shared';

export type MeasurementFamilyCode = string;

export type MeasurementUnitCode = string;

export type MeasurementUnit = {
  code: MeasurementUnitCode;
  convert_from_standard: {
    operator: string;
    value: string;
  }[];
  labels: LabelCollection;
  symbol: string;
};

export type MeasurementFamily = {
  code: MeasurementFamilyCode;
  is_locked: boolean;
  labels: LabelCollection;
  standard_unit_code: string;
  units: MeasurementUnit[];
};

export type MeasurementValue = {
  amount: string;
  unit: MeasurementUnitCode;
};
