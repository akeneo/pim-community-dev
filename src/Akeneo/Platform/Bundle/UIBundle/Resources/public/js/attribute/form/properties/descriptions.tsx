import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {AttributeDescriptionsApp} from '@akeneo-pim-community/settings-ui';

const propertyAccessor = require('pim/common/property');

class Descriptions extends BaseView {
  initialize(): void {
    BaseView.prototype.initialize.apply(this, arguments);
  }

  render(): any {
    const onChange = (newDescriptions: {[key: string]: string}) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, 'descriptions', newDescriptions);
      this.setData(data);
    };

    ReactDOM.render(<AttributeDescriptionsApp defaultValue={this.getFormData().descriptions || {}} onChange={onChange} />, this.el);
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = Descriptions;
