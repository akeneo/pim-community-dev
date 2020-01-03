'use strict';

import {
  fetchProductDataQualityEvaluation
} from 'akeneodataqualityinsights-react';

import {has as _has, uniq as _uniq, pick as _pick} from 'lodash';

const $ = require('jquery');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const UserContext = require('pim/user-context');

interface Recommendation {
  attributes: string[];
}

class AttributeFilterAllImprovableAttributes extends BaseForm
{
  async filterValues(values: any) {
    const productId: number = this.getFormData().meta.id;
    const missing_attributes = await this.fetchProductEvaluation(productId);
    const valuesToFill = _pick(values, missing_attributes);

    return $.Deferred().resolve(valuesToFill).promise();
  }

  async fetchProductEvaluation(productId: number) {
    const scope = UserContext.get('catalogScope');
    const locale = UserContext.get('catalogLocale');

    const data = await fetchProductDataQualityEvaluation(productId);

    let attributes: string[] = [];
    if (_has(data, ['consistency', scope, locale, 'recommendations'])) {
      data.consistency[scope][locale].recommendations.map((recommendation: Recommendation) => {
        Array.prototype.push.apply(attributes, recommendation.attributes);
      })
    }

    return _uniq(attributes);
  }

  getCode() {
    return 'all_improvable_attributes';
  }

  getLabel() {
    return __('akeneo_data_quality_insights.product_edit_form.attribute_filter.all_improvable_attributes');
  }

  isVisible() {
    return true;
  }
}

export = AttributeFilterAllImprovableAttributes;
