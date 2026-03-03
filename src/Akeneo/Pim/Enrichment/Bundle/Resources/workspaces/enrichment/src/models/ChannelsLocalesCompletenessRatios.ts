type ChannelsLocalesCompletenessRatios = {
  [channelLabel: string]: {
    channelRatio: number;
    localesRatios: {
      [localeLabel: string]: number;
    };
  };
};

export {ChannelsLocalesCompletenessRatios};
