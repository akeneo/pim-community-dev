import {EventsHash} from 'backbone';
import * as _ from 'underscore';

import BaseView = require('pimui/js/view/base');
const FormBuilder = require('pim/form-builder');
const __ = require('oro/translator');
const SecurityContext = require('pim/security-context');

const template = require('pimee/template/product/form/duplicate');

interface Config {
  form: string;
}

class Duplicate extends BaseView {
  private readonly template = _.template(template);

  private readonly config: Config;

  constructor(options: {config: Config}) {
    super({...options, ...{className: 'AknDropdown-menuLink duplicate', tagName: 'button'}});

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  public events(): EventsHash {
    return {
      click: this.openFormModal,
    };
  }

  public render(): BaseView {
    if (
      SecurityContext.isGranted('pim_enrich_product_create') &&
      'product' === this.getFormData().meta.model_type &&
      true === this.getFormData().meta.is_owner &&
      null === this.getFormData().parent
    ) {
      this.$el.html(this.template({__: __}));
    }

    return BaseView.prototype.render.apply(this, arguments);
  }

  private openFormModal() {
    return FormBuilder.build(this.config.form).then((modal: any) => {
      modal.setFamilyCode(this.getFormData().family);
      modal.setProductUuidToDuplicate(this.getFormData().meta.id);
      modal.setProductIdentifierToDuplicate(this.getFormData().identifier);
      modal.open();
    });
  }
}

export = Duplicate;
