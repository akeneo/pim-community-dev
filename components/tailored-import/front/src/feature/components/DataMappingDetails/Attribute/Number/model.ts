import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../../models';
import {DecimalSeparator} from '../../common/DecimalSeparatorField';

type NumberSourceConfiguration = {decimal_separator: DecimalSeparator};

const getDefaultNumberSourceConfiguration = (): NumberSourceConfiguration => ({decimal_separator: '.'});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

type NumberTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_configuration: NumberSourceConfiguration;
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
  source_configuration: getDefaultNumberSourceConfiguration(),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isNumberSourceConfiguration = (sourceConfiguration: any): sourceConfiguration is NumberSourceConfiguration =>
  'decimal_separator' in sourceConfiguration;

const isNumberTarget = (target: Target): target is NumberTarget =>
  'attribute' === target.type &&
  null !== target.source_configuration &&
  isNumberSourceConfiguration(target.source_configuration);

export type {NumberTarget, NumberSourceConfiguration};
export {getDefaultNumberTarget, isNumberTarget};
