import {ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID} from '@akeneo-pim-community/data-quality-insights/src/application/constant';

const BaseView = require('pimui/js/view/base');

class AttributesTabContent extends BaseView {
  public render() {
    this.el.insertAdjacentHTML(
      'beforeend',
      `
      <div id="${ATTRIBUTES_TAB_CONTENT_CONTAINER_ELEMENT_ID}"></div>
    `
    );
    return this;
  }
}

export default AttributesTabContent;
