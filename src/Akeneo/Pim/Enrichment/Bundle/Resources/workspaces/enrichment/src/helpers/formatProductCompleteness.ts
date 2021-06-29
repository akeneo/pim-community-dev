import {ChannelsLocalesCompletenessRatios} from '../models';

type BackendLocaleCompleteness = {
  label: string;
  completeness: {
    ratio: number;
  };
};

type BackendProductCompleteness = {
  labels: {
    [locale: string]: string;
  };
  stats: {
    average: number;
  };
  locales: {
    [locale: string]: BackendLocaleCompleteness;
  };
};

const formatProductCompleteness = (
  rawProductCompleteness: BackendProductCompleteness[],
  catalogLocale: string
): ChannelsLocalesCompletenessRatios => {
  return rawProductCompleteness.reduce(
    (formattedCompleteness: ChannelsLocalesCompletenessRatios, channelCompleteness: BackendProductCompleteness) => {
      formattedCompleteness[channelCompleteness.labels[catalogLocale]] = {
        channelRatio: channelCompleteness.stats.average,
        localesRatios: Object.values(channelCompleteness.locales).reduce(
          (localesRatios: {[localeLabel: string]: number}, localeCompleteness: BackendLocaleCompleteness) => {
            localesRatios[localeCompleteness.label] = localeCompleteness.completeness.ratio;
            return localesRatios;
          },
          {}
        ),
      };
      return formattedCompleteness;
    },
    {}
  );
};

export {formatProductCompleteness};
