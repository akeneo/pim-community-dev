import {uuid} from 'akeneo-design-system';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {Source} from '../../../models';

type QualityScoreSource = {
  uuid: string;
  code: 'quality_score';
  type: 'property';
  locale: ChannelCode;
  channel: LocaleCode;
  operations: {};
  selection: {type: 'code'};
};

const getDefaultQualityScoreSource = (channel: ChannelCode, locale: LocaleCode): QualityScoreSource => ({
  uuid: uuid(),
  code: 'quality_score',
  type: 'property',
  channel,
  locale,
  operations: {},
  selection: {type: 'code'},
});

const isQualityScoreSource = (source: Source): source is QualityScoreSource =>
  'quality_score' === source.code && 'property' === source.type;

export {getDefaultQualityScoreSource, isQualityScoreSource};
export type {QualityScoreSource};
