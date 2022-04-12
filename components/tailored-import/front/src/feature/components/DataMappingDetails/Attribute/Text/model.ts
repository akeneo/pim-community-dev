import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../../models';

const getDefaultTextSourceParameter = () => null;
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

type TextTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_parameter: null;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultTextTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TextTarget => ({
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  source_parameter: getDefaultTextSourceParameter(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isTextTarget = (target: Target): target is TextTarget =>
  'attribute' === target.type && null === target.source_parameter;

export type {TextTarget};
export {getDefaultTextTarget, isTextTarget};
