import {ChannelReference, ChannelCode} from 'akeneopimenrichmentassetmanager/platform/model/channel/channel';
import {LocaleReference} from 'akeneopimenrichmentassetmanager/platform/model/channel/locale';
import {AttributeCode, Attribute} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
// import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';

// type LocaleCompleteness = {
//   completeness: {
//     required: number;
//     missing: number;
//   };
// };

// type ChannelCompteness = {
//   channel: ChannelCode;
//   labels: Labels;
//   locales: {[key: string]: LocaleCompleteness};
// };
// export type Completeness = ChannelCompteness[];

export type Meta = {
  attributes_for_this_level: AttributeCode[];
  level: number | null;
  // completeness: Completeness;
};

export type CategoryCode = string;

export type LegacyValue = {
  data: any;
  scope: ChannelReference;
  locale: LocaleReference;
  attribute: AttributeCode;
};
export type LegacyValueCollection = {
  [key: string]: LegacyValue[];
};

export type Product = {
  meta: Meta;
  values: LegacyValueCollection;
  categories: CategoryCode[];
};
export type FamilyCode = string;
type AttributeRequirements = {
  [key: string]: AttributeCode[];
};

export type Family = {
  code: FamilyCode;
  attribute_requirements: AttributeRequirements;
};

export type Value = {
  attribute: Attribute;
  locale: LocaleReference;
  channel: ChannelReference;
  data: any;
  editable: boolean;
};

export type ValueCollection = Value[];

export const isValueComplete = (value: Value, family: Family | null, channel: ChannelCode) => {
  if (null === family) return true;
  if (undefined === family.attribute_requirements[channel]) return true;

  return !family.attribute_requirements[channel].includes(value.attribute.code) || value.data.length > 0;
};
