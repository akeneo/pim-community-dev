import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, isTargetNotEmptyAction, Target, TargetEmptyAction, TargetNotEmptyAction} from '../../../../models';

type MultiReferenceEntityTarget = {
  code: string;
  reference_data_name?: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: null;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultMultiReferenceEntityTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MultiReferenceEntityTarget => ({
  code: attribute.code,
  reference_data_name: attribute.reference_data_name,
  type: 'attribute',
  attribute_type: attribute.type,
  locale,
  channel,
  source_configuration: null,
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isMultiReferenceEntityTarget = (target: Target): target is MultiReferenceEntityTarget =>
  'attribute' === target.type &&
  'akeneo_reference_entity_collection' === target.attribute_type &&
  null === target.source_configuration &&
  isTargetNotEmptyAction(target.action_if_not_empty);

export type {MultiReferenceEntityTarget};
export {getDefaultMultiReferenceEntityTarget, isMultiReferenceEntityTarget};
