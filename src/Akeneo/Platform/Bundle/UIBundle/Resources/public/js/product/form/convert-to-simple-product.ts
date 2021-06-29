import {EventsHash} from 'backbone';

import BaseView = require('pimui/js/view/base');
const Dialog = require('pim/dialog');
const __ = require('oro/translator');
const messenger = require('oro/messenger');
const Routing = require('routing');
const router = require('pim/router');

interface Config {
  url: string;
}

class ConvertToSimpleProduct extends BaseView {
  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options, ...{className: 'AknDropdown-menuLink', tagName: 'button'}});

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      click: this.convert,
    };
  }

  public render(): BaseView {
    const formData = this.getFormData();
    if ('product' === formData.meta.model_type && null !== formData.parent && this.isAuthorized()) {
      this.$el.html(__('pim_enrich.entity.product.module.convert_variant_to_simple.label'));
    }

    return BaseView.prototype.render.apply(this, arguments);
  }

  protected isAuthorized(): boolean {
    return true;
  }

  private convert() {
    return Dialog.confirm(
      'pim_enrich.entity.product.module.convert_variant_to_simple.dialog.content',
      'pim_enrich.entity.product.module.convert_variant_to_simple.dialog.title',
      () => {
        router.showLoadingMask();

        fetch(
          Routing.generate(this.config.url, {
            id: this.getFormData().meta.id,
          }),
          {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
            },
            method: 'POST',
          }
        )
          .then(response => {
            if (response.ok) {
              messenger.notify('success', __('pim_enrich.entity.product.flash.update.success'));
            } else {
              messenger.notify('error', __('pim_enrich.entity.product.flash.update.fail'));
            }
          })
          .catch(e => {
            console.error(e);
            messenger.notify('error', __('pim_enrich.entity.product.flash.update.fail'));
          })
          .finally(() => {
            router.hideLoadingMask();
            router.reloadPage();
          });
      },
      null,
      null,
      'pim_common.confirm',
      'products'
    );
  }
}

export = ConvertToSimpleProduct;
