import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {getDefaultNumberTarget, NumberTarget} from "../components/TargetDetails/Number/model";
import {Attribute} from "./Attribute";
import {getDefaultTextTarget, TextTarget} from "../components/TargetDetails/Text/model";
import {getDefaultTextSource} from "@akeneo-pim-enterprise/tailored-export/lib/components/SourceDetails/Text/model";

type TargetAction = 'set' | 'add';
type TargetEmptyAction = 'clear' | 'skip';

type AttributeTarget =
  | NumberTarget
  | TextTarget
;

type PropertyTarget = {
  code: string;
  type: 'property';
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
  configuration: any
};

type Target = AttributeTarget | PropertyTarget;

const getDefaultAttributeTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): AttributeTarget => {
  return getDefaultNumberTarget(attribute, channel, locale);
  switch (attribute.type) {
    case 'pim_catalog_number':
      return getDefaultNumberTarget(attribute, channel, locale);
    case 'pim_catalog_textarea':
    case 'pim_catalog_text':
      return getDefaultTextTarget(attribute, channel, locale);
    default:
      throw new Error(`Invalid attribute target "${attribute.type}"`);
  }
};

const createPropertyTarget = (code: string): PropertyTarget => ({
  code,
  type: 'property',
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
  configuration: {},
});

const isAttributeTarget = (target: Target): target is AttributeTarget =>
  'attribute' === target.type && 'locale' in target && 'channel' in target;

const isPropertyTarget = (target: Target): target is PropertyTarget => 'property' === target.type;

export type {AttributeTarget, PropertyTarget, Target, TargetAction, TargetEmptyAction};
export {getDefaultAttributeTarget, createPropertyTarget, isAttributeTarget, isPropertyTarget};
