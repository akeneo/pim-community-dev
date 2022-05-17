import BaseView = require('pimui/js/view/base');
import {ATTRIBUTE_OPTIONS_AUTO_SORT} from 'akeneopimstructure/js/attribute-option';
import AttributeOptionsApp from './AttributeOptionsApp';

const __ = require('oro/translator');
const propertyAccessor = require('pim/common/property');

class Choices extends BaseView {
  private config: any;

  initialize(config: any): void {
    this.config = config.config;
    BaseView.prototype.initialize.apply(this, arguments);
  }

  configure(): JQueryPromise<any> {
    if (this.isActive()) {
      this.trigger('tab:register', {
        code: this.code,
        label: __(this.config.label),
      });
    }

    window.addEventListener(ATTRIBUTE_OPTIONS_AUTO_SORT, ((event: CustomEvent) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, 'auto_option_sorting', event.detail.autoSortOptions);
      this.setData(data);
    }) as EventListener);

    return super.configure();
  }

  render(): any {
    if (!this.isActive()) {
      return;
    }

    const formData = this.getFormData();

    this.renderReact(
      AttributeOptionsApp,
      {
        attributeId: formData.meta.id,
        attributeCode: formData.code,
        autoSortOptions: formData.auto_option_sorting,
      },
      this.el
    );
    return this;
  }

  private isActive() {
    return this.config.activeForTypes.includes((this.getRoot() as any).getType());
  }
}

export = Choices;
