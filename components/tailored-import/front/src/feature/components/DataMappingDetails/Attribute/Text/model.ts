import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

type TextTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: null;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultTextTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TextTarget => ({
  code: attribute.code,
  type: 'attribute',
  attribute_type: attribute.type,
  locale,
  channel,
  source_configuration: null,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isTextTarget = (target: Target): target is TextTarget =>
  'attribute' === target.type && null === target.source_configuration;

export type {TextTarget};
export {getDefaultTextTarget, isTextTarget};
