const DataCollector = require('pim/data-collector');

class CampaignFetcher {
  private analyticsUrl: string = 'pim_analytics_data_collect';

  private campaign: string|null = null;

  public async fetch() {
    if (null === this.campaign) {
      const data = await DataCollector.collect(this.analyticsUrl);
      this.campaign = `${data.pim_edition}${data.pim_version}`;
    }

    return this.campaign;
  }
}

export = new CampaignFetcher();
