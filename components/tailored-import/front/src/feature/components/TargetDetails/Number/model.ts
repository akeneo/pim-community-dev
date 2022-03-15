import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type NumberSeparator = keyof typeof availableDecimalSeparators;
type NumberConfiguration = {decimal_separator: NumberSeparator};

const getDefaultNumberConfiguration = (): NumberConfiguration => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultNumberConfiguration = (configuration?: NumberConfiguration): boolean => '.' === configuration?.decimal_separator;

type NumberTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  configuration: NumberConfiguration;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultNumberTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): NumberTarget => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  configuration: getDefaultNumberConfiguration(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isNumberConfiguration = (configuration: any): configuration is NumberConfiguration => 'decimal_separator' in configuration;
const isNumberDecimalSeparator = (separator: any): separator is NumberSeparator =>
  separator in availableDecimalSeparators;

const isNumberTarget = (target: Target): target is NumberTarget => isNumberConfiguration(target.configuration);

export type {NumberTarget, NumberConfiguration};
export {
  availableDecimalSeparators,
  getDefaultNumberTarget,
  isDefaultNumberConfiguration,
  isNumberDecimalSeparator,
  isNumberTarget,
};
