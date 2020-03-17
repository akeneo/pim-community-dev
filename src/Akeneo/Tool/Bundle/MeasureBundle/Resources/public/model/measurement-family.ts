import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneomeasure/model/locale';

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

type LabelCollection = {
  [locale: string]: string;
};

type Operation = {
  operator: string;
  value: string;
};

type UnitCode = string;

type Unit = {
  code: UnitCode;
  labels: LabelCollection;
  symbol: string;
  convert_from_standard: Operation[];
};

type MeasurementFamilyCode = string;

type MeasurementFamily = {
  code: MeasurementFamilyCode;
  labels: LabelCollection;
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

const setUnitLabel = (
  measurementFamily: MeasurementFamily,
  unitCode: UnitCode,
  locale: LocaleCode,
  value: string
): MeasurementFamily => {
  const unit = getUnit(measurementFamily, unitCode);

  if (undefined === unit) return measurementFamily;

  unit.labels = {...unit.labels, [locale]: value};

  return {...measurementFamily, units: {...measurementFamily.units, [unitCode]: unit}};
};

const getUnit = (measurementFamily: MeasurementFamily, unitCode: UnitCode): Unit | undefined =>
  measurementFamily.units.find(unit => unit.code === unitCode);

const getStandardUnit = (measurementFamily: MeasurementFamily): Unit => {
  const unit = getUnit(measurementFamily, measurementFamily.standard_unit_code);

  if (undefined === unit) throw Error('Measurement family should always have a standard unit');

  return unit;
};

const getStandardUnitLabel = (measurementFamily: MeasurementFamily, locale: LocaleCode) =>
  getUnitLabel(getStandardUnit(measurementFamily), locale);

const filterOnLabelOrCode = (searchValue: string, locale: LocaleCode) => (entity: {
  code: string;
  labels: LabelCollection;
}): boolean =>
  -1 !== entity.code.toLowerCase().indexOf(searchValue.toLowerCase()) ||
  (undefined !== entity.labels[locale] &&
    -1 !== entity.labels[locale].toLowerCase().indexOf(searchValue.toLowerCase()));

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
  Unit,
  MeasurementFamily,
  MeasurementFamilyCode,
  getMeasurementFamilyLabel,
  setMeasurementFamilyLabel,
  getUnitLabel,
  setUnitLabel,
  getStandardUnit,
  getStandardUnitLabel,
  filterOnLabelOrCode,
  sortMeasurementFamily,
};
