import {BackendChannelData, BackendCompletenessData} from '../domain';
import {ChannelsLocalesCompletenessRatios} from '@akeneo-pim-community/enrichment/src/models';

const convertBackendDashboardCompletenessData = (
  data: BackendCompletenessData,
  catalogLocale: string
): ChannelsLocalesCompletenessRatios => {
  let result: ChannelsLocalesCompletenessRatios = {};
  Object.entries(data).map(([channelCode, channelData]: [string, BackendChannelData]) => {
    const divider: number = channelData.total;
    const channelRatio: number = divider === 0 ? 0 : Math.floor((channelData.complete / divider) * 100);
    const channelLabel = channelData.labels[catalogLocale] || `[${channelCode}]`;

    let localesRatios: {[localeTranslation: string]: number} = {};
    Object.entries(channelData.locales).map(([localeLabel, localeCompleteCount]: [string, number]) => {
      localesRatios[localeLabel] = divider === 0 ? 0 : Math.floor((localeCompleteCount / divider) * 100);
    });

    result[channelLabel] = {
      channelRatio,
      localesRatios,
    };
  });

  return result;
};

export {convertBackendDashboardCompletenessData, BackendChannelData};
