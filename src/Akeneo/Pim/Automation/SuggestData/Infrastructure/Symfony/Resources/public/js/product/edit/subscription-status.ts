import BaseView = require('pimenrich/js/view/base');
import * as _ from "underscore";
import {getSubscriptionStatus} from "../fetcher/subscription-fetcher";

const __ = require('oro/translator');
const template = require('pimee/template/form/product/subscription-status');

interface SubscriptionStatusInterface {
  subscription_id: string;
}

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
    const productId = this.getFormData().meta.id;

    getSubscriptionStatus(productId).then((subscriptionStatus: SubscriptionStatusInterface) => {
      this.$el.html(
        this.template({
          subscriptionStatusTitle: __('akeneo_suggest_data.product.edit.subscription_status_title'),
          subscriptionStatus: '' !== subscriptionStatus.subscription_id ? __('pim_common.yes') : __('pim_common.no'),
        })
      );
    });

    return this;
  }
}

export = SubscriptionStatus;
