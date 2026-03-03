import BaseView = require('pimui/js/view/base');
import React from 'react';
import ReactDOM from 'react-dom';
import {AttributeGuidelinesApp} from '@akeneo-pim-community/settings-ui';

const propertyAccessor = require('pim/common/property');

class Guidelines extends BaseView {
  initialize(): void {
    BaseView.prototype.initialize.apply(this, arguments);
  }

  render(): any {
    const onChange = (newGuidelines: {[key: string]: string}) => {
      const data = this.getFormData();
      propertyAccessor.updateProperty(data, 'guidelines', newGuidelines);
      this.setData(data);
    };

    ReactDOM.render(
      <AttributeGuidelinesApp defaultValue={this.getFormData().guidelines || {}} onChange={onChange} />,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = Guidelines;
