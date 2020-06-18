import {validateCampaignData} from './../validator/campaignData';

const DataCollector = require('pim/data-collector');

class CampaignFetcher {
  static analyticsUrl: string = 'pim_analytics_data_collect';

  static cloudVersion: string = 'serenity';

  static campaign: string | null = null;

  static async fetch(): Promise<string> {
    if (null === this.campaign) {
      const data = await DataCollector.collect(this.analyticsUrl);

      validateCampaignData(data);

      if (this.cloudVersion === data.pim_edition.toLowerCase()) {
        this.campaign = data.pim_edition as string;
      } else {
        this.campaign = `${data.pim_edition}${data.pim_version}`;
      }
    }

    return this.campaign;
  }
}

export {CampaignFetcher};
