import {isLabels, isString} from 'akeneoassetmanager/domain/model/utils';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

export type Label = string;
type LabelCollection = {
  [locale: string]: Label;
};
export default LabelCollection;

export const denormalizeLabelCollection = (labelCollection: any) => {
  if (!isLabels(labelCollection)) {
    throw new Error('LabelCollection expect only values as {"en_US": "My label"} to be created');
  }

  return {...labelCollection};
};

export const setLabelInCollection = (labelCollection: LabelCollection, locale: LocaleCode, label: Label) => ({
  ...labelCollection,
  [locale]: label,
});

export const hasLabelInCollection = (labelCollection: LabelCollection, locale: LocaleCode) =>
  isString(labelCollection[locale]) && labelCollection[locale].length > 0;

export const getLabelInCollection = (
  labelCollection: LabelCollection,
  locale: LocaleCode,
  fallbackOnCode: boolean = true,
  code: string = ''
) => {
  if (!hasLabelInCollection(labelCollection, locale) && !fallbackOnCode) {
    return '';
  }

  return hasLabelInCollection(labelCollection, locale) ? labelCollection[locale] : `[${code}]`;
};

export const emptyLabelCollection = () => ({});
