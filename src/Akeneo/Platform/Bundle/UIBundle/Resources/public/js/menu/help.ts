import * as _ from 'underscore';
const {BaseForm: BaseView} = require('pimui/js/view/base');

const __ = require('oro/translator');
const DataCollector = require('pim/data-collector');

const template = require('pim/template/menu/help');

/**
 * Extension for displaying help link with version numbers
 */
class Help extends BaseView {
  private readonly analyticsUrl: string = 'pim_analytics_data_collect';

  private readonly template = _.template(template);

  constructor() {
    super({className: 'AknHeader-menuItemContainer'});
  }

  public render(): typeof BaseView {
    this.getUrl().then((url: string) => {
      this.$el.empty().append(
        this.template({
          helper: __('pim_menu.tab.help.helper'),
          title: __('pim_menu.tab.help.title'),
          url,
        })
      );
    });

    return BaseView.prototype.render.apply(this, arguments);
  }

  private getUrl(): Promise<string> {
    return DataCollector.collect(this.analyticsUrl).then((data: any) => {
      const {pim_version, pim_edition} = data;

      let version = `v${pim_version.split('.')[0]}`;
      let campaign = `${pim_edition}${pim_version}`;

      // CE master, serenity
      if (pim_version.split('.').length === 1) {
        version = 'serenity';
        campaign = 'serenity';
      }

      const url = new URL(`https://help.akeneo.com/pim/${version}/index.html`);
      url.searchParams.append('utm_source', 'akeneo-app');
      url.searchParams.append('utm_medium', 'interrogation-icon');
      url.searchParams.append('utm_campaign', campaign);

      return url.href;
    });
  }
}

export default Help;
