import {BackendChannelData, BackendCompletenessData, ChannelsLocalesCompletenesses} from '../domain';

const convertBackendDashboardCompletenessData = (
  data: BackendCompletenessData,
  catalogLocale: string
): ChannelsLocalesCompletenesses => {
  let result: ChannelsLocalesCompletenesses = {};
  Object.entries(data).map(([channelCode, channelData]: [string, BackendChannelData]) => {
    const divider: number = channelData.total * Object.keys(channelData.locales).length;
    const channelScore: number = divider === 0 ? 0 : Math.round((channelData.complete / divider) * 100);
    const channelLabel = channelData.labels[catalogLocale] || `[${channelCode}]`;

    let localesRatios: {[localeTranslation: string]: number} = {};
    Object.entries(channelData.locales).map(([localeLabel, localeCompleteCount]: [string, number]) => {
      const divider: number = channelData.total;
      localesRatios[localeLabel] = divider === 0 ? 0 : Math.round((localeCompleteCount / divider) * 100);
    });

    result[channelLabel] = {
      channelRatio: channelScore,
      locales: localesRatios,
    };
  });

  return result;
};

export {convertBackendDashboardCompletenessData, BackendChannelData};
