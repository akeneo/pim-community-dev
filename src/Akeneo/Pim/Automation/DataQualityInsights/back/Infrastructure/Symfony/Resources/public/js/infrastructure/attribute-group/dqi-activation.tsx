import ReactDOM from 'react-dom';
import React from 'react';
import {AttributeGroupDQIActivation} from '@akeneo-pim-community/data-quality-insights/src';

const BaseView = require('pimui/js/view/base');

class DQIActivation extends BaseView {
  public render() {
    ReactDOM.render(<AttributeGroupDQIActivation groupCode={this.getFormData()['code']} />, this.el);

    return this;
  }
}

export default DQIActivation;
