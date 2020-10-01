import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneomeasure/model/locale';
import {LabelCollection} from 'akeneomeasure/model/label-collection';
import {Unit, UnitCode, getUnitLabel} from 'akeneomeasure/model/unit';
import {Operation} from 'akeneomeasure/model/operation';
import {Direction} from 'akeneomeasure/model/direction';

type MeasurementFamilyCode = string;

type MeasurementFamily = {
  code: MeasurementFamilyCode;
  labels: LabelCollection;
  standard_unit_code: string;
  units: Unit[];
  is_locked: boolean;
};

const getMeasurementFamilyLabel = (measurementFamily: MeasurementFamily, locale: LocaleCode) =>
  getLabel(measurementFamily.labels, locale, measurementFamily.code);

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
  const units = measurementFamily.units.map((unit: Unit) => {
    if (unitCode !== unit.code) {
      return unit;
    }

    return {
      ...unit,
      labels: {...unit.labels, [locale]: value},
    };
  });

  return {...measurementFamily, units: units};
};

const setUnitOperations = (measurementFamily: MeasurementFamily, unitCode: UnitCode, operations: Operation[]) => {
  const units = measurementFamily.units.map((unit: Unit) => {
    if (unitCode !== unit.code) {
      return unit;
    }

    return {
      ...unit,
      convert_from_standard: operations,
    };
  });

  return {...measurementFamily, units: units};
};

const addUnit = (measurementFamily: MeasurementFamily, unit: Unit): MeasurementFamily => ({
  ...measurementFamily,
  units: [...measurementFamily.units, unit],
});

const removeUnit = (measurementFamily: MeasurementFamily, unitCode: UnitCode): MeasurementFamily => ({
  ...measurementFamily,
  units: measurementFamily.units.filter((unit) => unit.code !== unitCode),
});

const setUnitSymbol = (measurementFamily: MeasurementFamily, unitCode: UnitCode, value: string): MeasurementFamily => {
  const units = measurementFamily.units.map((unit: Unit) => {
    if (unitCode !== unit.code) {
      return unit;
    }

    return {
      ...unit,
      symbol: value,
    };
  });

  return {...measurementFamily, units: units};
};

const getUnit = (measurementFamily: MeasurementFamily, unitCode: UnitCode): Unit | undefined =>
  measurementFamily.units.find((unit) => unit.code === unitCode);

const getUnitIndex = (measurementFamily: MeasurementFamily, unitCode: UnitCode): number => {
  const unit = getUnit(measurementFamily, unitCode);

  if (undefined === unit) return -1;

  return measurementFamily.units.indexOf(unit);
};

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
  MeasurementFamily,
  MeasurementFamilyCode,
  getMeasurementFamilyLabel,
  setMeasurementFamilyLabel,
  getUnit,
  getUnitIndex,
  setUnitLabel,
  setUnitSymbol,
  setUnitOperations,
  addUnit,
  removeUnit,
  getStandardUnit,
  getStandardUnitLabel,
  filterOnLabelOrCode,
  sortMeasurementFamily,
};
