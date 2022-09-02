import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction, isTargetNotEmptyAction} from '../../../../models';

type MultiSelectTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: null;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultMultiSelectTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MultiSelectTarget => ({
  code: attribute.code,
  type: 'attribute',
  attribute_type: attribute.type,
  locale,
  channel,
  source_configuration: null,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isMultiSelectTarget = (target: Target): target is MultiSelectTarget =>
  'attribute' === target.type &&
  'pim_catalog_multiselect' === target.attribute_type &&
  null === target.source_configuration &&
  isTargetNotEmptyAction(target.action_if_not_empty);

export type {MultiSelectTarget};
export {getDefaultMultiSelectTarget, isMultiSelectTarget};
