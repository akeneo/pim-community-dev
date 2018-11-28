/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {EventsHash} from 'backbone';
import * as $ from 'jquery';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';
import {getSubscriptionStatus} from '../../fetcher/subscription';
import SubscriptionStatus from '../../model/subscription-status';

const __ = require('oro/translator');
const Mediator = require('oro/mediator');
const Messenger = require('oro/messenger');
const Routing = require('routing');
const template = require('akeneo/suggest-data/template/product-edit-form/subscription-status-switcher');
const templateReadOnly = require('akeneo/suggest-data/template/product-edit-form/subscription-status-switcher-read-only');

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
 */
class SubscriptionStatusSwitcher extends BaseView {
  protected currentStatus: boolean;
  private readonly template: any = _.template(template);
  private readonly templateReadOnly: any = _.template(templateReadOnly);
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
  public configure(): JQueryPromise<any> {
    this.listenTo(Mediator, 'pim_enrich:form:entity:post_save', this.render);

    return BaseView.prototype.configure.apply(this, arguments);
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
  public render(): any {
    const productId = this.getFormData().meta.id;

    // TODO: must use product identifier and not id
    return getSubscriptionStatus(productId).then((subscriptionStatus: SubscriptionStatus): Promise<BaseView> => {
      this.currentStatus = subscriptionStatus.isSubscribed;
      let isReadOnlyMode = false;
      let errorMessage = '';

      if (!subscriptionStatus.isConnectionActive) {
        return BaseView.prototype.render.apply(this);
      }

      if (subscriptionStatus.isProductVariant) {
        return BaseView.prototype.render.apply(this);
      }

      if (!subscriptionStatus.isIdentifiersMappingValid) {
        isReadOnlyMode = true;
        errorMessage = 'akeneo_suggest_data.entity.product_subscription.module.product_edit_form.invalid_mapping';
      } else if (!subscriptionStatus.hasFamily) {
        isReadOnlyMode = true;
        errorMessage = 'akeneo_suggest_data.entity.product_subscription.module.product_edit_form.family_required';
      } else if (!subscriptionStatus.isMappingFilled) {
        isReadOnlyMode = true;
        errorMessage = 'akeneo_suggest_data.entity.product_subscription.module.product_edit_form.no_identifier_filled';
      }

      if (isReadOnlyMode) {
        this.$el.html(
          this.templateReadOnly({
            subscriptionStatusTitle: 'akeneo_suggest_data.product.edit.subscription_status_title',
            status: this.currentStatus ? 'enabled' : 'disabled',
            statusLabel: this.currentStatus ? 'Enabled' : 'Disabled',
            errorMessage,
            __,
          }),
        );
        this.delegateEvents();

        return BaseView.prototype.render.apply(this);
      }

      this.$el.html(
        this.template({
          subscriptionStatusTitle: 'akeneo_suggest_data.product.edit.subscription_status_title',
          hasSubscribed: this.currentStatus,
          enabledLabel: 'Enabled',
          disabledLabel: 'Disabled',
          isSwitchEnabled: true,
          __,
        }),
      );
      this.delegateEvents();

      return BaseView.prototype.render.apply(this);
    });
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
   * Subscribes the edited product to Franklin.
   */
  private subscribeProduct(): void {
    // TODO: must use product identifier and not id
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
   * Unsubscribe the edited product to Franklin.
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
