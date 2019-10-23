import {getLabel} from 'pimui/js/i18n';
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';

export type AttributeCode = string;
export type AttributeGroupCode = string;
export type ReferenceDataName = string;
export type Attribute = {
  code: AttributeCode;
  labels: {
    [locale: string]: string;
  };
  group: AttributeGroupCode;
  isReadOnly: boolean;
  referenceDataName: ReferenceDataName;
  sort_order: number;
};

export const getAttributeLabel = (attribute: Attribute, locale: LocaleCode) => {
  return getLabel(attribute.labels, locale, attribute.code);
};
