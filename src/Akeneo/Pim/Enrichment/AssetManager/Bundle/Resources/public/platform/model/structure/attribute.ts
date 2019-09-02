import {LocaleCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {getLabel} from 'pimui/js/i18n';

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
};

export const getAttributeLabel = (attribute: Attribute, locale: LocaleCode) => {
  return getLabel(attribute.labels, locale, attribute.code);
};
