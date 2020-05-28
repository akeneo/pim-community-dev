import {LocaleCode} from 'akeneomeasure/model/locale';
import {LabelCollection} from 'akeneomeasure/model/label-collection';
import {Operation} from 'akeneomeasure/model/operation';
import {getLabel} from 'pimui/js/i18n';

type UnitCode = string;

type Unit = {
  code: UnitCode;
  labels: LabelCollection;
  symbol: string;
  convert_from_standard: Operation[];
};

const getUnitLabel = (unit: Unit, locale: LocaleCode) => getLabel(unit.labels, locale, unit.code);

export {Unit, UnitCode, getUnitLabel};
