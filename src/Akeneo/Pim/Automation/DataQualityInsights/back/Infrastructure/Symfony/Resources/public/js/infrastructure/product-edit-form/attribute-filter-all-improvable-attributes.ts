'use strict';

import {
  fetchProductDataQualityEvaluation,
  CriterionEvaluationResult,
  ProductEvaluation
} from 'akeneodataqualityinsights-react';

import {get as _get, has as _has, pick as _pick, uniq as _uniq} from 'lodash';

const $ = require('jquery');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const UserContext = require('pim/user-context');

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

    const data: ProductEvaluation = await fetchProductDataQualityEvaluation(productId);

    let attributes: string[] = [];
    const axisCriteriaPath = ['consistency', scope, locale, 'criteria'];

    if (_has(data, axisCriteriaPath)) {
      // @ts-ignore
      _get(data, axisCriteriaPath).map((criterion: CriterionEvaluationResult) => {
        attributes.push(...criterion.improvable_attributes);
      });
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
