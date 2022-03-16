import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type NumberSeparator = keyof typeof availableDecimalSeparators;
type NumberParameters = {decimal_separator: NumberSeparator};

const getDefaultNumberConfiguration = (): NumberParameters => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultNumberParameters = (parameters?: NumberParameters): boolean => '.' === parameters?.decimal_separator;

type NumberTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  parameters: NumberParameters;
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
  parameters: getDefaultNumberConfiguration(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isNumberParameters = (parameters: any): parameters is NumberParameters => 'decimal_separator' in parameters;
const isNumberDecimalSeparator = (separator: any): separator is NumberSeparator =>
  separator in availableDecimalSeparators;

const isNumberTarget = (target: Target): target is NumberTarget => isNumberParameters(target.parameters);

export type {NumberTarget, NumberParameters};
export {
  availableDecimalSeparators,
  getDefaultNumberTarget,
  isDefaultNumberParameters,
  isNumberDecimalSeparator,
  isNumberTarget,
};
