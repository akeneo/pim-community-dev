import {EventsHash} from 'backbone';
import BaseView = require('pimenrich/js/view/base');
import * as _ from 'underscore';
import {getSubscriptionStatus} from '../../fetcher/subscription';
import SubscriptionStatus from '../../model/subscription-status';

const __ = require('oro/translator');
const Messenger = require('oro/messenger');
const Routing = require('routing');
const template = require('pimee/template/product-edit-form/subscription-status-switcher');

interface Config {
  createProductSubscriptionFailMessage: string;
  createProductSubscriptionSuccessMessage: string;
  deleteProductSubscriptionFailMessage: string;
  deleteProductSubscriptionSuccessMessage: string;
}

/**
 * Meta that displays is a product is Subscribed to Franklin or not.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SubscriptionStatusSwitcher extends BaseView {
  protected currentStatus: boolean;
  private readonly template: any = _.template(template);
  private readonly config: Config;

  /**
   * {@inheritdoc}
   */
  constructor(options: { config: Config }) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      'click .AknDropdown-menuLink': 'updateStatus',
    };
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    const productId = this.getFormData().meta.id;

    getSubscriptionStatus(productId).then((subscriptionStatus: SubscriptionStatus) => {
      this.currentStatus = subscriptionStatus.isSubscribed;
      this.$el.html(
        this.template({
          subscriptionStatusTitle: __('akeneo_suggest_data.product.edit.subscription_status_title'),
          hasSubscribed: this.currentStatus,
          enabledLabel: __('Enabled'),
          disabledLabel: __('Disabled'),
        }),
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
    const newStatus = event.currentTarget.dataset.status === 'enabled';

    if (true === newStatus && false === this.currentStatus) {
      this.subscribeProduct();
    }

    if (false === newStatus && true === this.currentStatus) {
      this.unsubscribeProduct();
    }
  }

  /**
   * Subscribes the edited product to PIM.ai.
   */
  private subscribeProduct(): void {
    $.ajax({
      method: 'POST',
      url: Routing.generate('akeneo_suggest_data_subscribe', {productId: this.getFormData().meta.id}),
    }).done(() => {
      Messenger.notify(
        'success',
        __(this.config.createProductSubscriptionSuccessMessage),
      );
    }).fail((xhr: any) => {
      const response = xhr.responseJSON;
      let errorMessage = this.config.createProductSubscriptionFailMessage;

      if (undefined !== response && undefined !== response.errors) {
        errorMessage = response.errors;
      }

      Messenger.notify('error', __(errorMessage));
    }).always(() => {
      this.render();
    });
  }

  /**
   * Unsubscribe the edited product to PIM.ai.
   */
  private unsubscribeProduct(): void {
    $.ajax({
      method: 'DELETE',
      url: Routing.generate('akeneo_suggest_data_unsubscribe', {productId: this.getFormData().meta.id}),
    }).done(() => {
      Messenger.notify(
        'success',
        __(this.config.deleteProductSubscriptionSuccessMessage),
      );
    }).fail((xhr: any) => {
      const response = xhr.responseJSON;
      let errorMessage = this.config.deleteProductSubscriptionFailMessage;

      if (undefined !== response && undefined !== response.errors) {
        errorMessage = response.errors;
      }

      Messenger.notify('error', __(errorMessage));
    }).always(() => {
      this.render();
    });
  }
}

export = SubscriptionStatusSwitcher;
