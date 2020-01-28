'use strict';

import {
  fetchProductDataQualityEvaluation
} from 'akeneodataqualityinsights-react';

import {has as _has, uniq as _uniq, pick as _pick} from 'lodash';

interface Recommendation {
  attributes: string[];
}

const $ = require('jquery');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const UserContext = require('pim/user-context');

class AttributeFilterAllMissingAttributes extends BaseForm
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
    if (_has(data, ['enrichment', scope, locale, 'recommendations'])) {
      data.enrichment[scope][locale].recommendations.map((recommendation: Recommendation) => {
        Array.prototype.push.apply(attributes, recommendation.attributes);
      })
    }

    return _uniq(attributes);
  }

  getCode() {
    return 'all_missing_attributes';
  }

  getLabel() {
    return __('akeneo_data_quality_insights.product_edit_form.attribute_filter.all_missing_attributes');
  }

  isVisible() {
    return true;
  }
}

export = AttributeFilterAllMissingAttributes;
