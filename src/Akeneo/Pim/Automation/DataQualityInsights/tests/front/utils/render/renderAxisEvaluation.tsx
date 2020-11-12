import React from 'react';
import {render} from '@testing-library/react';
import {Provider} from 'react-redux';
import {createStoreWithInitialState} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/store/productEditFormStore';
import AxisEvaluation from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/AxisEvaluation';
import Evaluation from '@akeneo-pim-community/data-quality-insights/src/domain/Evaluation.interface';
import Criterion from '@akeneo-pim-community/data-quality-insights/src/application/component/ProductEditForm/TabContent/DataQualityInsights/Criterion';

const renderEnrichmentEvaluation = (evaluation: Evaluation) => {
  return renderWithRedux(
    <AxisEvaluation axis={'enrichment'} evaluation={evaluation}>
      <Criterion code={'completeness_of_non_required_attributes'} />
      <Criterion code={'completeness_of_required_attributes'} />
    </AxisEvaluation>
  );
};

const renderConsistencyEvaluation = (evaluation: Evaluation) => {
  return renderWithRedux(
    <AxisEvaluation axis={'consistency'} evaluation={evaluation}>
      <Criterion code={'consistency_spelling'} />
      <Criterion code={'consistency_textarea_lowercase_words'} />
      <Criterion code={'consistency_textarea_uppercase_words'} />
      <Criterion code={'consistency_text_title_formatting'} />
      <Criterion code={'not_applicable_criterion'} />
    </AxisEvaluation>
  );
};

const renderWithRedux = (ui: React.ReactElement) => {
  const initialState = {
    catalogContext: {channel: 'ecommerce', locale: 'en_US'},
    product: {
      categories: [],
      enabled: true,
      family: 'led_tvs',
      identifier: null,
      meta: {
        id: 1,
        label: {},
        attributes_for_this_level: [],
        level: null,
        model_type: 'product',
      },
      created: null,
      updated: null,
    },
    families: {
      led_tvs: {
        code: 'led_tvs',
        attributes: [
          {
            code: 'description',
            type: 'text',
            group: '',
            validation_rule: null,
            validation_regexp: null,
            wysiwyg_enabled: null,
            localizable: true,
            scopable: true,
            labels: {
              en_US: 'Product description',
            },
            is_read_only: true,
            meta: {id: 1},
          },
        ],
        attribute_as_label: 'description',
        labels: {
          en_US: 'LED TVs',
        },
      },
    },
  };
  return render(<Provider store={createStoreWithInitialState(initialState)}>{ui}</Provider>);
};

export {renderEnrichmentEvaluation, renderConsistencyEvaluation};
