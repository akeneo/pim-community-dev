const {BaseForm: BaseView} = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {ATTRIBUTE_OPTIONS_AUTO_SORT, AttributeOptionsApp} from 'akeneopimstructure/js/attribute-option';

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

    ReactDOM.render(
      <AttributeOptionsApp
        attributeId={this.getFormData().meta.id}
        autoSortOptions={this.getFormData().auto_option_sorting}
      />,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }

  private isActive() {
    return this.config.activeForTypes.includes((this.getRoot() as any).getType());
  }
}

export default Choices;
