import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

type SimpleSelectTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: null;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultSimpleSelectTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): SimpleSelectTarget => ({
  code: attribute.code,
  type: 'attribute',
  attribute_type: attribute.type,
  locale,
  channel,
  source_configuration: null,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isSimpleSelectTarget = (target: Target): target is SimpleSelectTarget =>
  'attribute' === target.type &&
  'pim_catalog_simpleselect' === target.attribute_type &&
  null === target.source_configuration;

export type {SimpleSelectTarget};
export {getDefaultSimpleSelectTarget, isSimpleSelectTarget};
