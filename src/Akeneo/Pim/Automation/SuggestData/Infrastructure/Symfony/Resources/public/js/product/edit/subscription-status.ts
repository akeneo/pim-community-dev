import BaseView = require('pimenrich/js/view/base');
import * as _ from "underscore";

const __ = require('oro/translator');
const template = require('pimee/template/form/product/subscription-status');

/**
 * Meta that displays is a product is Subscribed to Franklin or not.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SubscriptionStatus extends BaseView {
  readonly template: any = _.template(template);

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Object }) {
    super({
      ...options,
      ...{className: 'AknColumn-block'},
    });
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    this.$el.html(
      this.template({
        subscribeStatusTitle: __('akeneo_suggest_data.product.edit.subscription_status_title'),
        subscribeStatus: 'Yes or No',
      })
    );

    return this;
  }
}

export = SubscriptionStatus;
