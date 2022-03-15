import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

const availableDecimalSeparators = {'.': 'dot', ',': 'comma', '٫‎': 'arabic_comma'};

type NumberSeparator = keyof typeof availableDecimalSeparators;
type NumberSelection = {decimal_separator: NumberSeparator};

const getDefaultNumberSelection = (): NumberSelection => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultNumberSelection = (selection?: NumberSelection): boolean => '.' === selection?.decimal_separator;

type NumberTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  selection: NumberSelection;
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
  selection: getDefaultNumberSelection(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isNumberSelection = (selection: any): selection is NumberSelection => 'decimal_separator' in selection;
const isNumberDecimalSeparator = (separator: any): separator is NumberSeparator =>
  separator in availableDecimalSeparators;

const isNumberTarget = (target: Target): target is NumberTarget =>
  isNumberSelection(target.selection);

export type {NumberTarget, NumberSelection};
export {
  availableDecimalSeparators,
  getDefaultNumberTarget,
  isDefaultNumberSelection,
  isNumberDecimalSeparator,
  isNumberTarget,
};
