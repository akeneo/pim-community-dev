import LabelCollection, {
  denormalizeLabelCollection,
  emptyLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import OptionCode, {denormalizeOptionCode} from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';
import LocaleReference, {localeReferenceIsEmpty} from 'akeneoassetmanager/domain/model/locale-reference';
import {getLabel} from 'pimui/js/i18n';

export type Option = {
  code: OptionCode;
  labels: LabelCollection;
};

export const createOptionFromNormalized = (normalizedOption: any): Option => {
  return {
    code: denormalizeOptionCode(normalizedOption.code),
    labels: denormalizeLabelCollection(normalizedOption.labels),
  };
};

export const getOptionLabel = (option: Option, locale: LocaleReference): string => {
  return localeReferenceIsEmpty(locale) ? `[${option.code}]` : getLabel(option.labels, locale, option.code);
};

export const createEmptyOption = (): Option => {
  return {
    code: '',
    labels: emptyLabelCollection(),
  };
};
