import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
const translate = require('oro/translator');

class TableOptionsTab extends BaseView {
  private config: any;

  initialize(config: any): void {
    this.config = config.config;
    BaseView.prototype.initialize.apply(this, arguments);
  }

  configure(): JQueryPromise<any> {
    if (this.isActive()) {
      this.trigger('tab:register', {
        code: this.code,
        label: translate(this.config.label),
      });
    }

    return super.configure();
  }

  render(): any {
    if (!this.isActive()) {
      return;
    }

    ReactDOM.render(
      <div>Prout</div>
      /*<TableOptionsApp
        attributeId={this.getFormData().meta.id}
      />*/,
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

export = TableOptionsTab;
