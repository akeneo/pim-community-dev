import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../models';

type TextSelection = {decimal_separator: TextSeparator};

const getDefaultTextSelection = (): TextSelection => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

const isDefaultTextSelection = (selection?: TextSelection): boolean => '.' === selection?.decimal_separator;

type TextTarget = {
  uuid: string;
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  selection: TextSelection;
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
  selection: getDefaultTextSelection(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isTextSelection = (selection: any): selection is TextSelection => 'decimal_separator' in selection;

const isTextTarget = (target: Target): target is TextTarget =>
  isTextSelection(target.selection);

export type {TextTarget, TextSelection};
export {
  getDefaultTextTarget,
  isDefaultTextSelection,
  isTextTarget,
};
