import * as Backbone from 'backbone';
import * as _ from 'underscore';
import CampaignFetcher from 'akeneocommunicationchannel/fetcher/campaign';

const __ = require('oro/translator');
const CommunicationChannelTemplate = require('akeneo/template/menu/communication-channel');

class CommunicationChannel extends Backbone.View<any> {
  private baseUrl: string = 'https://help.akeneo.com/pim/serenity/updates/index.html';

  private source: string = 'akeneo-app';

  private medium: string = 'communication-icon';

  public render(): Backbone.View {
    const template = _.template(CommunicationChannelTemplate);
    this.$el.empty().append(
      template({
        title: __('akeneo_communication_channel.link.title'),
      })
    );

    this.setLink();

    return Backbone.View.prototype.render.apply(this, arguments);
  }

  private async setLink(): Promise<void> {
    const url = await this.buildUrl();
    this.$('a').attr('href', `${url.href}`);
  }

  private async buildUrl(): Promise<URL> {
    const campaign = await CampaignFetcher.fetch();
    const url = new URL(this.baseUrl);
    url.searchParams.append('utm_source', this.source);
    url.searchParams.append('utm_medium', this.medium);
    url.searchParams.append('utm_campaign', campaign);

    return url;
  }
}

export = CommunicationChannel;
