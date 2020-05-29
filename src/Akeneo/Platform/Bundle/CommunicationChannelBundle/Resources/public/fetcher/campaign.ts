const DataCollector = require('pim/data-collector');

class CampaignFetcher {
  private analyticsUrl: string = 'pim_analytics_data_collect';

  private cloudVersion: string = 'serenity';

  private campaign: string | null = null;

  public async fetch() {
    if (null === this.campaign) {
      const data = await DataCollector.collect(this.analyticsUrl);

      if (this.cloudVersion === data.pim_edition.toLowerCase()) {
        this.campaign = data.pim_edition as string;
      } else {
        this.campaign = `${data.pim_edition}${data.pim_version}`;
      }
    }

    return this.campaign;
  }
}

export = new CampaignFetcher();
