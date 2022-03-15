import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

type TextConfiguration = {};

const getDefaultTextConfiguration = (): TextConfiguration => ({});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultTextConfiguration = (configuration?: TextConfiguration): boolean => ({} === configuration);

type TextTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  configuration: TextConfiguration;
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
  configuration: getDefaultTextConfiguration(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isTextConfiguration = (configuration: any): configuration is TextConfiguration => 'decimal_separator' in configuration;

const isTextTarget = (target: Target): target is TextTarget => isTextConfiguration(target.configuration);

export type {TextTarget, TextConfiguration};
export {
  getDefaultTextTarget,
  isDefaultTextConfiguration,
  isTextTarget,
};
