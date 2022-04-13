import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../../models';

const getDefaultSimpleSelectSourceConfiguration = () => null;
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

type SimpleSelectTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_configuration: null;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultSimpleSelectTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): SimpleSelectTarget => ({
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  source_configuration: getDefaultSimpleSelectSourceConfiguration(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isSimpleSelectTarget = (target: Target): target is SimpleSelectTarget =>
  'attribute' === target.type && null === target.source_configuration;

export type {SimpleSelectTarget};
export {getDefaultSimpleSelectTarget, isSimpleSelectTarget};
