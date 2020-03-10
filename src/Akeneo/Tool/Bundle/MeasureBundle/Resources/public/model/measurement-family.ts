import {getLabel} from 'pimui/js/i18n';

type Operation = {
  operator: string;
  value: string;
};

type Unit = {
  code: string;
  labels: {
    [locale: string]: string;
  };
  symbol: string;
  convert_from_standard: Operation[];
};

export type MeasurementFamily = {
  code: string;
  labels: {
    [locale: string]: string;
  };
  standard_unit_code: string;
  units: Unit[];
};

export const getMeasurementFamilyLabel = (measurementFamily: MeasurementFamily, locale: string) =>
  getLabel(measurementFamily.labels, locale, measurementFamily.code);

export const getUnitLabel = (unit: Unit, locale: string) => getLabel(unit.labels, locale, unit.code);

export const getStandardUnitLabel = (measurementFamily: MeasurementFamily, locale: string) => {
  const unit = measurementFamily.units.find(unit => unit.code === measurementFamily.standard_unit_code);

  if (undefined === unit) return `[${measurementFamily.standard_unit_code}]`;

  return getUnitLabel(unit, locale);
};
