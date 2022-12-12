import * as _ from 'underscore';
import Backbone from 'backbone';

import BaseView = require('pimui/js/view/base');
const LoadingMask = require('oro/loading-mask');
const Routing = require('routing');
const __ = require('oro/translator');
const messenger = require('oro/messenger');
const router = require('pim/router');

const template = require('pimee/template/product/form/duplicate/modal');

interface Config {
  labels: {
    title: string;
    subTitle: string;
  };
  picture: string;
  successMessage: string;
  successMessageWithAttributeCodes: string;
  editRoute: string;
  postUrl: string;
}

interface DuplicatedProductResponse {
  duplicated_product: any;
  unique_attribute_codes: string[];
  identifier_generator_warnings?: { path: string, message: string }[];
}

class DuplicateModal extends BaseView {
  private readonly template = _.template(template);

  private productUuidToDuplicate: string;
  private productIdentifierToDuplicate: string;

  private readonly config: Config;

  public constructor(options: {config: Config}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  public setProductUuidToDuplicate(productUuid: string) {
    this.productUuidToDuplicate = productUuid;
  }

  public setProductIdentifierToDuplicate(productIdentifier: string) {
    this.productIdentifierToDuplicate = productIdentifier;
  }

  public setFamilyCode(familyCode: string) {
    this.getFormModel().set('family', familyCode);
  }

  public render(): BaseView {
    this.$el.html(
      this.template({
        fields: null,
      })
    );

    this.renderExtensions();

    return BaseView.prototype.render.apply(this, arguments);
  }

  public open() {
    const modal = new (Backbone as any).BootstrapModal({
      title: __(this.config.labels.title, {
        product_identifier: this.productIdentifierToDuplicate || this.productUuidToDuplicate,
      }),
      subtitle: __(this.config.labels.subTitle),
      picture: this.config.picture,
      content: '',
      okText: __('pim_common.save'),
      okCloses: false,
    });
    modal.open();
    this.setElement(modal.$('.modal-body')).render();

    modal.on('cancel', () => modal.close());

    modal.on('ok', () => {
      this.confirmModal(modal);
    });
  }

  private async confirmModal(modal: any) {
    const duplicatedProductResponse: DuplicatedProductResponse = await this.duplicate();

    modal.close();

    this.notifySuccessMessage(duplicatedProductResponse);

    router.redirectToRoute(this.config.editRoute, {uuid: duplicatedProductResponse.duplicated_product.meta.id});
  }

  private notifySuccessMessage(duplicatedProductResponse: DuplicatedProductResponse) {
    if (duplicatedProductResponse.identifier_generator_warnings) {
      const normalizedWarnings = duplicatedProductResponse.identifier_generator_warnings.map(warning => {
        return `${warning.path}: ${warning.message} `;
      });

      messenger.notify(
        'warning',
        __('pim_enrich.entity.product.flash.update.identifier_warning'),
        normalizedWarnings
      );
    }

    if (duplicatedProductResponse.unique_attribute_codes.length === 0) {
      messenger.notify(
        'success',
        __(this.config.successMessage, {
          product_identifier: this.productIdentifierToDuplicate,
        })
      );
    } else {
      messenger.notify(
        'success',
        __(this.config.successMessageWithAttributeCodes, {
          product_identifier: this.productIdentifierToDuplicate,
          unique_attribute_codes: duplicatedProductResponse.unique_attribute_codes.join(','),
        })
      );
    }
  }

  private duplicate() {
    const loadingMask = new LoadingMask();
    this.$el.empty().append(loadingMask.render().$el.show());

    return $.ajax({
      url: Routing.generate(this.config.postUrl, {
        uuid: this.productUuidToDuplicate,
      }),
      type: 'POST',
      data: JSON.stringify(this.getFormData()),
    })
      .fail((response: any) => this.fail(response))
      .always(() => loadingMask.remove());
  }

  private fail(response: any) {
    (this.getRoot() as any).validationErrors = response.responseJSON
      ? this.normalize(response.responseJSON)
      : [
          {
            message: __('pim_enrich.entity.fallback.generic_error'),
          },
        ];

    this.render();
  }

  /**
   * Normalize the path property for validation errors
   * @param  {Array} errors
   * @return {Array}
   */
  private normalize(errors: any) {
    const values = errors.values || [];

    return values.map((error: any) => {
      if (!error.path) {
        error.path = error.attribute;
      }

      return error;
    });
  }
}

export = DuplicateModal;
