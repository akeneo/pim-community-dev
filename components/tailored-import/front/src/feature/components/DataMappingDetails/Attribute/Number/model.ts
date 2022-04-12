import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../../models';
import {DecimalSeparator} from '../../common/DecimalSeparatorField';

type NumberSourceParameter = {decimal_separator: DecimalSeparator};

const getDefaultNumberSourceParameter = (): NumberSourceParameter => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

type NumberTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_parameter: NumberSourceParameter;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultNumberTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): NumberTarget => ({
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  source_parameter: getDefaultNumberSourceParameter(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isNumberSourceParameter = (sourceParameter: any): sourceParameter is NumberSourceParameter =>
  'decimal_separator' in sourceParameter;

const isNumberTarget = (target: Target): target is NumberTarget =>
  'attribute' === target.type && null !== target.source_parameter && isNumberSourceParameter(target.source_parameter);

export type {NumberTarget, NumberSourceParameter};
export {getDefaultNumberTarget, isNumberTarget};
