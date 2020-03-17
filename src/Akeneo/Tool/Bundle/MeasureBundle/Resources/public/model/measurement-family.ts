import {getLabel} from 'pimui/js/i18n';

type LocaleCode = string;

enum Direction {
  Ascending = 'Ascending',
  Descending = 'Descending',
}

enum Operator {
  MUL = 'mul',
  DIV = 'div',
  ADD = 'add',
  SUB = 'sub',
}

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

type MeasurementFamilyCode = string;

type MeasurementFamily = {
  code: MeasurementFamilyCode;
  labels: {
    [locale: string]: string;
  };
  standard_unit_code: string;
  units: Unit[];
};

const getMeasurementFamilyLabel = (measurementFamily: MeasurementFamily, locale: LocaleCode) =>
  getLabel(measurementFamily.labels, locale, measurementFamily.code);

const getUnitLabel = (unit: Unit, locale: LocaleCode) => getLabel(unit.labels, locale, unit.code);

const setMeasurementFamilyLabel = (
  measurementFamily: MeasurementFamily,
  locale: LocaleCode,
  value: string
): MeasurementFamily => ({...measurementFamily, labels: {...measurementFamily.labels, [locale]: value}});

const getStandardUnitLabel = (measurementFamily: MeasurementFamily, locale: LocaleCode) => {
  const unit = measurementFamily.units.find(unit => unit.code === measurementFamily.standard_unit_code);

  if (undefined === unit) return `[${measurementFamily.standard_unit_code}]`;

  return getUnitLabel(unit, locale);
};

const filterMeasurementFamily = (
  measurementFamily: MeasurementFamily,
  searchValue: string,
  locale: LocaleCode
): boolean =>
  -1 !== measurementFamily.code.toLowerCase().indexOf(searchValue.toLowerCase()) ||
  (undefined !== measurementFamily.labels[locale] &&
    -1 !== measurementFamily.labels[locale].toLowerCase().indexOf(searchValue.toLowerCase()));

const sortMeasurementFamily = (sortDirection: Direction, locale: LocaleCode, sortColumn: string) => (
  first: MeasurementFamily,
  second: MeasurementFamily
) => {
  const directionInverter = sortDirection === Direction.Descending ? -1 : 1;

  switch (sortColumn) {
    case 'label':
      return (
        directionInverter *
        getMeasurementFamilyLabel(first, locale).localeCompare(getMeasurementFamilyLabel(second, locale))
      );
    case 'code':
      return directionInverter * first.code.localeCompare(second.code);
    case 'standard_unit':
      return (
        directionInverter * getStandardUnitLabel(first, locale).localeCompare(getStandardUnitLabel(second, locale))
      );
    case 'unit_count':
      return directionInverter * (first.units.length - second.units.length);
    default:
      return 0;
  }
};

export {
  Direction,
  Operator,
  MeasurementFamily,
  MeasurementFamilyCode,
  getMeasurementFamilyLabel,
  setMeasurementFamilyLabel,
  getStandardUnitLabel,
  filterMeasurementFamily,
  sortMeasurementFamily,
};
