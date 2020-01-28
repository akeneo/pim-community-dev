import {AttributeCode, Attribute} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Labels} from 'akeneoassetmanager/platform/model/label';
import {Family} from 'akeneoassetmanager/platform/model/structure/family';
import {ChannelCode} from 'akeneoassetmanager/domain/model/channel';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import LocaleReference from 'akeneoassetmanager/domain/model/locale-reference';

type LocaleCompleteness = {
  completeness: {
    required: number;
    missing: number;
  };
};

type ChannelCompleteness = {
  channel: ChannelCode;
  labels: Labels;
  locales: {[key: string]: LocaleCompleteness};
};
export type Completeness = ChannelCompleteness[];

export type Meta = {
  attributes_for_this_level: AttributeCode[];
  level: number | null;
  completeness: Completeness;
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

export type Value = {
  attribute: Attribute;
  locale: LocaleReference;
  channel: ChannelReference;
  data: any;
  editable: boolean;
};

export type ValueCollection = Value[];

export const isValueComplete = (value: Value, family: Family | null, channel: ChannelCode): boolean => {
  if (null === family) return true;
  if (undefined === family.attributeRequirements[channel]) return true;

  return !family.attributeRequirements[channel].includes(value.attribute.code) || value.data.length > 0;
};

export const hasValues = (values: Value[]): boolean => {
  return 0 !== values.length;
};

export const sameValue = (first: Value, second: Value): boolean => {
  return (
    first.locale === second.locale && first.channel === second.channel && first.attribute.code === second.attribute.code
  );
};
