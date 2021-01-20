import {LocaleCode} from '../model/locale';
import {LabelCollection} from '../model/label-collection';
import {Operation} from '../model/operation';
import {getLabel} from '../shared/tools/i18n';

type UnitCode = string;

type Unit = {
  code: UnitCode;
  labels: LabelCollection;
  symbol: string;
  convert_from_standard: Operation[];
};

const getUnitLabel = (unit: Unit, locale: LocaleCode) => getLabel(unit.labels, locale, unit.code);

export {getUnitLabel};
export type {Unit, UnitCode};
