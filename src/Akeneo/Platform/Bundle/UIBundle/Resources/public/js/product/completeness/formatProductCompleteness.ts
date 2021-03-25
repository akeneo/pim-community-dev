import {ChannelsLocalesCompletenesses} from "@akeneo-pim-community/activity/src/domain";

const formatProductCompleteness = (rawProductCompleteness: any[], catalogLocale: string): ChannelsLocalesCompletenesses[] => {
  return rawProductCompleteness.reduce((formattedCompleteness, channelCompleteness) => {
    formattedCompleteness[channelCompleteness.labels[catalogLocale]] = {
      channelRatio: channelCompleteness.stats.average,
      locales: Object.values(channelCompleteness.locales).reduce((localesRatios, locale) => {
        localesRatios[locale.label] = locale.completeness.ratio;
        return localesRatios;
      }, {}),
    };
    return formattedCompleteness;
  }, {});
};

export {formatProductCompleteness};
