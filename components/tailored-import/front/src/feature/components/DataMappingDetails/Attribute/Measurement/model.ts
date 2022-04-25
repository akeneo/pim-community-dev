import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';
import {DecimalSeparator} from '../../common/DecimalSeparatorField';

type MeasurementSourceConfiguration = {
  decimal_separator: DecimalSeparator;
  unit: string;
};

const getDefaultMeasurementSourceConfiguration = (attribute: Attribute): MeasurementSourceConfiguration => ({
  decimal_separator: '.',
  unit: attribute.default_metric_unit ?? '',
});

type MeasurementTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  source_configuration: MeasurementSourceConfiguration;
  action_if_not_empty: TargetNotEmptyAction;
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
  source_configuration: getDefaultMeasurementSourceConfiguration(attribute),
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isMeasurementSourceConfiguration = (
  sourceConfiguration: any
): sourceConfiguration is MeasurementSourceConfiguration =>
  'decimal_separator' in sourceConfiguration && 'unit' in sourceConfiguration;

const isMeasurementTarget = (target: Target): target is MeasurementTarget => {
  return (
    'attribute' === target.type &&
    null !== target.source_configuration &&
    isMeasurementSourceConfiguration(target.source_configuration)
  );
};

export type {MeasurementTarget, MeasurementSourceConfiguration};
export {getDefaultMeasurementTarget, isMeasurementTarget};
