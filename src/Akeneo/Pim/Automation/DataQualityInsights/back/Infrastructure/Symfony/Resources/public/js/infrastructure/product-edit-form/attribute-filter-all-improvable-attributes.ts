'use strict';

import {
  fetchProductDataQualityEvaluation,
  fetchProductModelEvaluation,
  CriterionEvaluationResult,
  ProductEvaluation,
} from '@akeneo-pim-community/data-quality-insights/src/index';

import {get as _get, has as _has, pick as _pick, uniq as _uniq} from 'lodash';

const $ = require('jquery');
const __ = require('oro/translator');
const BaseForm = require('pim/form');
const UserContext = require('pim/user-context');

class AttributeFilterAllImprovableAttributes extends BaseForm {
  async filterValues(values: any) {
    const product = this.getFormData();
    const missing_attributes = await this.fetchProductEvaluation(product);
    const valuesToFill = _pick(values, missing_attributes);

    return $.Deferred()
      .resolve(valuesToFill)
      .promise();
  }

  async fetchProductEvaluation(product: any) {
    const scope = UserContext.get('catalogScope');
    const locale = UserContext.get('catalogLocale');

    const fetcher =
      product.meta.model_type === 'product_model' ? fetchProductModelEvaluation : fetchProductDataQualityEvaluation;

    const data: ProductEvaluation = await fetcher(product.meta.id);

    let attributes: string[] = [];
    const axisCriteriaPath = [scope, locale];

    const consistencyCriteria = [
      'consistency_textarea_uppercase_words',
      'consistency_textarea_lowercase_words',
      'consistency_spelling',
      'consistency_attribute_spelling',
      'consistency_attribute_option_spelling',
    ];

    if (_has(data, axisCriteriaPath)) {
      const criteria = _get(data, axisCriteriaPath)
        // @ts-ignore
        .filter((criterionEvaluation: CriterionEvaluationResult) =>
          consistencyCriteria.includes(criterionEvaluation.code)
        );
      // @ts-ignore
      criteria.map((criterion: CriterionEvaluationResult) => {
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
