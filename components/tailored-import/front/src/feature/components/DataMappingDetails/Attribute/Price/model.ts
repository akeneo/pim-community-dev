import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';
import {DecimalSeparator} from '../../common/DecimalSeparatorField';

type PriceSourceConfiguration = {
  decimal_separator: DecimalSeparator;
  currency: null | string;
};

const getDefaultPriceSourceConfiguration = (attribute: Attribute): PriceSourceConfiguration => ({
  decimal_separator: '.',
  currency: null,
});

type PriceTarget = {
  code: string;
  reference_data_name?: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: PriceSourceConfiguration;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultPriceTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): PriceTarget => ({
  code: attribute.code,
  type: 'attribute',
  attribute_type: attribute.type,
  locale,
  channel,
  source_configuration: getDefaultPriceSourceConfiguration(attribute),
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isPriceSourceConfiguration = (sourceConfiguration: any): sourceConfiguration is PriceSourceConfiguration =>
  'decimal_separator' in sourceConfiguration && 'currency' in sourceConfiguration;

const isPriceTarget = (target: Target): target is PriceTarget =>
  'attribute' === target.type &&
  'pim_catalog_price_collection' === target.attribute_type &&
  null !== target.source_configuration &&
  isPriceSourceConfiguration(target.source_configuration);

export type {PriceTarget, PriceSourceConfiguration};
export {getDefaultPriceTarget, isPriceTarget};
