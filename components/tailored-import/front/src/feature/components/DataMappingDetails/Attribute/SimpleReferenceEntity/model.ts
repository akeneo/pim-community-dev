import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

type SimpleReferenceEntityTarget = {
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

const getDefaultSimpleReferenceEntityTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): SimpleReferenceEntityTarget => ({
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

const isSimpleReferenceEntityTarget = (target: Target): target is SimpleReferenceEntityTarget =>
  'attribute' === target.type &&
  'akeneo_reference_entity' === target.attribute_type &&
  null === target.source_configuration;

export type {SimpleReferenceEntityTarget};
export {getDefaultSimpleReferenceEntityTarget, isSimpleReferenceEntityTarget};
