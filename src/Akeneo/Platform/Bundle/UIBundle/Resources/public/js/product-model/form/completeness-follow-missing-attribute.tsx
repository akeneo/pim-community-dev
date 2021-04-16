import React, {FC} from 'react';
import {useScrollToAttribute, Product} from '@akeneo-pim-community/enrichment';

const BaseView = require('pimui/js/view/base');
const mediator = require('oro/mediator');

const FollowMissingAttribute: FC<{product: Product}> = ({product}) => {
  useScrollToAttribute(product);

  return <></>;
};

class CompletenessFollowMissingAttribute extends BaseView {
  configure() {
    this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:before', () => {
      mediator.trigger('ATTRIBUTES_LOADING');
    });
    this.listenTo(this.getRoot(), 'pim_enrich:form:attributes:render:after', () => {
      mediator.trigger('ATTRIBUTES_LOADED');
    });

    return super.configure();
  }

  render() {
    const product = this.getFormData();

    this.renderReact(FollowMissingAttribute, {product}, this.el);

    return this;
  }
}

export = CompletenessFollowMissingAttribute;
