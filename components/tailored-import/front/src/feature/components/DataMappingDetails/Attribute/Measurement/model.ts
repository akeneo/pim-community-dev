import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetAction, TargetEmptyAction} from '../../../../models';
import {DecimalSeparator} from '../../common/DecimalSeparatorField';

type MeasurementSourceParameter = {
  decimal_separator: DecimalSeparator;
  unit: string;
};

const getDefaultMeasurementSourceParameter = (attribute: Attribute): MeasurementSourceParameter => ({
  decimal_separator: '.',
  unit: attribute.default_metric_unit ?? '',
});
const getDefaultTargetAction = (): TargetAction => 'set';
const getDefaultTargetEmptyAction = (): TargetEmptyAction => 'skip';

type MeasurementTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_parameter: MeasurementSourceParameter;
  action_if_not_empty: TargetAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultMeasurementTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MeasurementTarget => ({
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  source_parameter: getDefaultMeasurementSourceParameter(attribute),
  action_if_not_empty: getDefaultTargetAction(),
  action_if_empty: getDefaultTargetEmptyAction(),
});

const isMeasurementSourceParameter = (sourceParameter: any): sourceParameter is MeasurementSourceParameter =>
  'decimal_separator' in sourceParameter && 'unit' in sourceParameter;

const isMeasurementTarget = (target: Target): target is MeasurementTarget => {
  return (
    'attribute' === target.type &&
    null !== target.source_parameter &&
    isMeasurementSourceParameter(target.source_parameter)
  );
};

export type {MeasurementTarget, MeasurementSourceParameter};
export {getDefaultMeasurementTarget, isMeasurementTarget};
