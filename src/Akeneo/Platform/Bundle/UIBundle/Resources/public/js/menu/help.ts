import * as _ from 'underscore';
import BaseView = require('pimui/js/view/base');

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

  public render(): BaseView {
    this.getVersion().then((version: string) => {
      this.$el.empty().append(
        this.template({
          helper: __('pim_menu.tab.help.helper'),
          title: __('pim_menu.tab.help.title'),
          version,
        })
      );
    });

    return BaseView.prototype.render.apply(this, arguments);
  }

  private getVersion(): Promise<string> {
    return DataCollector.collect(this.analyticsUrl).then((data: any) => {
      const {pim_version, pim_edition} = data;

      if ('Serenity' === pim_edition) {
        return pim_edition.toLowerCase();
      }

      return `v${pim_version.substring(0, 1)}`;
    });
  }
}

export = Help;
