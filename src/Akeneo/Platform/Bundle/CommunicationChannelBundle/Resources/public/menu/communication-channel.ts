import * as Backbone from 'backbone';
import * as _ from 'underscore';

const DataCollector = require('pim/data-collector');
const __ = require('oro/translator');

const CommunicationChannelTemplate = require('akeneo/template/menu/communication-channel');

class CommunicationChannel extends Backbone.View<any> {
  private analyticsUrl: string = 'pim_analytics_data_collect';

  private baseUrl: string =
    'https://help.akeneo.com/pim/serenity/updates/index.html?utm_source=akeneo-app&utm_medium=communication-icon&utm_campaign=';

  public render(): Backbone.View {
    const template: any = _.template(CommunicationChannelTemplate);
    this.$el.empty().append(
      template({
        title: __('pim_communication_channel.link.title'),
      })
    );

    return Backbone.View.prototype.render.apply(this, arguments);
  }

  public refresh(): void {
    this.getCampaign().then((campaign: string) => {
      this.$('a').attr('href', `${this.baseUrl}${campaign}`);
    });
  }

  private getCampaign() {
    return DataCollector.collect(this.analyticsUrl).then((data: any) => {
      const {pim_version, pim_edition} = data;

      return `${pim_edition}${pim_version}`;
    });
  }
}

export = CommunicationChannel;
