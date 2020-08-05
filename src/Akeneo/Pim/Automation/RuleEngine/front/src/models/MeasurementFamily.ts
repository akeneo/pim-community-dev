type MeasurementFamilyCode = string;
type MeasurementUnitCode = string;

type MeasurementUnit = {
  code: MeasurementUnitCode;
  labels: { [key: string]: string };
  convert_from_standard: { operator: string; value: string | number }[];
  symbol: string;
};

type MeasurementFamily = {
  code: MeasurementFamilyCode;
  labels: { [key: string]: string };
  standard_unit_code: string;
  units: MeasurementUnit[];
  is_locked: boolean;
};

export {
  MeasurementFamilyCode,
  MeasurementUnitCode,
  MeasurementUnit,
  MeasurementFamily,
};
