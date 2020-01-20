import {ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID,} from 'akeneodataqualityinsights-react';

const BaseView = require('pimui/js/view/base');

class AttributesTabContent extends BaseView {
  public render() {
    this.el.insertAdjacentHTML('beforeend', `
      <div id="${ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `);
    return this;
  }
}

export = AttributesTabContent;
