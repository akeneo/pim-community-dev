import {
  ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID,
  ATTRIBUTES_TAB_NAME,
  PRODUCT_TAB_CHANGED
} from 'akeneodataqualityinsights-react';

const BaseView = require('pimui/js/view/base');

class AttributesTabContent extends BaseView {
  public render() {
    this.el.insertAdjacentHTML('beforeend', `
      <div id="${ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `);

    this.showTabContent();
    return this;
  }

  private showTabContent() {
    window.dispatchEvent(new CustomEvent(PRODUCT_TAB_CHANGED, {detail: {
      currentTab: ATTRIBUTES_TAB_NAME,
    }}));
  }
}

export = AttributesTabContent;
