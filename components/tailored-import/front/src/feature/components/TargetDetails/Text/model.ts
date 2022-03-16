import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

type TextParameters = {};

const getDefaultTextParameters = (): TextParameters => ({});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultTextParameters = (parameters?: TextParameters): boolean => ({} === parameters);

type TextTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  parameters: TextParameters;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultTextTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TextTarget => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  parameters: getDefaultTextParameters(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isTextParameters = (parameters: any): boolean => ({} === parameters);

const isTextTarget = (target: Target): target is TextTarget => isTextParameters(target.parameters);

export type {TextTarget, TextParameters};
export {
  getDefaultTextTarget,
  isDefaultTextParameters,
  isTextTarget,
};
