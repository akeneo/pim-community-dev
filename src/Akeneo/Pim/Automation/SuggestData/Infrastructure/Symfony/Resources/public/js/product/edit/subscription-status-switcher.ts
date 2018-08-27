import * as _ from "underscore";
import {getSubscriptionStatus} from "../fetcher/subscription-fetcher";
import BaseView = require('pimenrich/js/view/base');
import {EventsHash} from "backbone";
import SubscriptionStatusInterface from "./subscription-status-interface";

const __ = require('oro/translator');
const messenger = require('oro/messenger');
const Routing = require('routing');
const template = require('pimee/template/form/product/subscription-status-switcher');

interface SubscriptionStatusSwitcherConfiguration {
  default_error_message: string;
  default_success_message: string;
}

/**
 * Meta that displays is a product is Subscribed to Franklin or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SubscriptionStatusSwitcher extends BaseView {
  readonly template: any = _.template(template);
  readonly config: SubscriptionStatusSwitcherConfiguration;
  protected currentStatus: boolean;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: SubscriptionStatusSwitcherConfiguration }) {
    super(options);

    this.config = {...this.config, ...options.config};
  };

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .AknDropdown-menuLink': 'updateStatus'
    };
  }

  /**
   * {@inheritdoc}
   */
  public render() {
    const productId = this.getFormData().meta.id;

    getSubscriptionStatus(productId).then((subscriptionStatus: SubscriptionStatusInterface) => {
      this.currentStatus = subscriptionStatus.is_subscribed;
      this.$el.html(
        this.template({
          subscriptionStatusTitle: __('akeneo_suggest_data.product.edit.subscription_status_title'),
          hasSubscribed: this.currentStatus,
          enabledLabel: __('Enabled'),
          disabledLabel: __('Disabled')
        })
      );
    });

    this.delegateEvents();

    return BaseView.prototype.render.apply(this, arguments);
  }

  /**
   * Update the current status of the product
   *
   * @param {Event} event
   */
  public updateStatus(event: { [key: string]: any }): void {
    let newStatus = event.currentTarget.dataset.status === 'enabled';

    // TODO APAI-142: For now, we don't manage to unsubscribe, only to subscribe.
    if (true === newStatus && false === this.currentStatus) {
      $.ajax({
        method: 'GET',
        url: Routing.generate('akeneo_suggest_data_push_product', {productId: this.getFormData().meta.id})
      }).done(() => {
        messenger.notify(
            'success',
            __(this.config.default_success_message)
        );
      }).fail((xhr: any) => {
        const response = xhr.responseJSON;
        let errorMessage = this.config.default_error_message;

        if (!_.isUndefined(response.error)) {
          errorMessage = response.error;
        }

        messenger.notify('error', __(errorMessage));
      }).always(() => {
        this.render();
      });
    }
  }
}

export = SubscriptionStatusSwitcher;
