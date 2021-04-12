import {ChannelsLocalesCompletenesses} from '@akeneo-pim-community/activity/src/domain';

type BackendProductCompleteness = {
  labels: {
    [locale: string]: string;
  };
  stats: {
    average: number;
  };
  locales: {
    [locale: string]: {
      label: string;
      completeness: {
        ratio: number;
      };
    };
  };
};

const formatProductCompleteness = (
  rawProductCompleteness: BackendProductCompleteness[],
  catalogLocale: string
): ChannelsLocalesCompletenesses => {
  return rawProductCompleteness.reduce((formattedCompleteness: ChannelsLocalesCompletenesses, channelCompleteness) => {
    formattedCompleteness[channelCompleteness.labels[catalogLocale]] = {
      channelRatio: channelCompleteness.stats.average,
      locales: Object.values(channelCompleteness.locales).reduce((localesRatios: any, locale) => {
        localesRatios[locale.label] = locale.completeness.ratio;
        return localesRatios;
      }, {}),
    };
    return formattedCompleteness;
  }, {});
};

export {formatProductCompleteness};
