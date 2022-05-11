import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Target, TargetNotEmptyAction, TargetEmptyAction} from '../../../../models';

const availableDateFormats = [
  'yyyy-mm-dd',
  'yyyy/mm/dd',
  'yyyy.mm.dd',
  'yy.m.dd',
  'mm-dd-yyyy',
  'mm/dd/yyyy',
  'mm.dd.yyyy',
  'dd-mm-yyyy',
  'dd/mm/yyyy',
  'dd.mm.yyyy',
  'dd-mm-yy',
  'dd.mm.yy',
  'dd/mm/yy',
  'dd-m-yy',
  'dd/m/yy',
  'dd.m.yy',
];

type DateFormat = typeof availableDateFormats[number];

type DateSourceConfiguration = {date_format: DateFormat};

const getDefaultDateSourceConfiguration = (): DateSourceConfiguration => ({date_format: 'yyyy-mm-dd'});

type DateTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  attribute_type: string;
  source_configuration: DateSourceConfiguration;
  action_if_not_empty: TargetNotEmptyAction;
  action_if_empty: TargetEmptyAction;
};

const getDefaultDateTarget = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): DateTarget => ({
  code: attribute.code,
  type: 'attribute',
  attribute_type: 'pim_catalog_date',
  locale,
  channel,
  source_configuration: getDefaultDateSourceConfiguration(),
  action_if_not_empty: 'set',
  action_if_empty: 'skip',
});

const isDateFormat = (dateFormat: unknown): dateFormat is DateFormat =>
  typeof dateFormat === 'string' && availableDateFormats.includes(dateFormat);

const isDateSourceConfiguration = (sourceConfiguration: any): sourceConfiguration is DateSourceConfiguration =>
  'date_format' in sourceConfiguration && isDateFormat(sourceConfiguration.date_format);

const isDateTarget = (target: Target): target is DateTarget =>
  'attribute' === target.type &&
  null !== target.source_configuration &&
  isDateSourceConfiguration(target.source_configuration);

export type {DateTarget, DateSourceConfiguration, DateFormat};
export {getDefaultDateTarget, isDateTarget, isDateFormat, availableDateFormats};
