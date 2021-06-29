import React from 'react';

import '@testing-library/jest-dom/extend-expect';
import {fireEvent} from '@testing-library/react';

import {RecommendationWithAttributesList} from '@akeneo-pim-community/data-quality-insights//src/application/component/ProductEditForm/TabContent/DataQualityInsights/';
import {Evaluation, Product} from '@akeneo-pim-community/data-quality-insights//src/domain';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from '@akeneo-pim-community/data-quality-insights//src/application/listener';
import {
  ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
} from '@akeneo-pim-community/data-quality-insights/src/application/constant';
import {renderWithProductEditFormContextHelper} from '../../utils/render';

beforeEach(() => {
  jest.resetModules();
  sessionStorage.clear();
});

window.dispatchEvent = jest.fn();

const Router = require('pim/router');

const evaluation: Evaluation = {
  criteria: [
    {
      code: 'consistency_spelling',
      improvable_attributes: ['description', 'title', 'weight'],
      rate: {
        value: 60,
        rank: 'D',
      },
      status: 'done',
    },
  ],
  rate: {
    value: 60,
    rank: 'D',
  },
};

describe('Click on improvable or missing attributes', () => {
  test('Simple product', async () => {
    const product = buildSimpleProduct();
    const {getAllByTestId} = renderWithData(
      product,
      <RecommendationWithAttributesList
        product={product}
        attributes={['description', 'title', 'weight']}
        axis={'consistency'}
        criterion={'consistency_spelling'}
        evaluation={evaluation}
      />
    );
    ['description', 'title', 'weight'].forEach((attributeCode: string, index: number) => {
      fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[index].parentElement);
      assertAttributeClickSendsAnEvent(attributeCode, index);
    });
  });

  test('Third level variant product', async () => {
    const product = buildThirdLevelProduct();
    const {getAllByTestId} = renderWithData(
      product,
      <RecommendationWithAttributesList
        product={product}
        attributes={['description', 'title', 'weight']}
        axis={'consistency'}
        criterion={'consistency_spelling'}
        evaluation={evaluation}
      />
    );
    await fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[0].parentElement);
    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('description');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate.mock.calls[0][0]).toBe('pim_enrich_product_model_edit');
    expect(Router.generate.mock.calls[0][1]).toMatchObject({id: 1111});

    fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[1].parentElement);
    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('title');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate.mock.calls[1][0]).toBe('pim_enrich_product_model_edit');
    expect(Router.generate.mock.calls[1][1]).toMatchObject({id: 2222});

    fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[2].parentElement);
    assertAttributeClickSendsAnEvent('weight', 0);
  });

  test('Second level variant product', async () => {
    const product = buildSecondLevelProduct();
    const {getAllByTestId} = renderWithData(
      product,
      <RecommendationWithAttributesList
        product={product}
        attributes={['description', 'title']}
        axis={'consistency'}
        criterion={'consistency_spelling'}
        evaluation={evaluation}
      />
    );
    fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[0].parentElement);
    expect(sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY)).toBe('description');
    expect(sessionStorage.getItem('current_column_tab')).toBe(PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    expect(Router.generate.mock.calls[0][0]).toBe('pim_enrich_product_model_edit');
    expect(Router.generate.mock.calls[0][1]).toMatchObject({id: 1111});

    fireEvent.click(getAllByTestId('dqiAttributeWithRecommendation')[1].parentElement);
    assertAttributeClickSendsAnEvent('title', 0);
  });
});

function assertAttributeClickSendsAnEvent(attributeCode: string, mockCallNumber: number) {
  const customEvents = window.dispatchEvent.mock.calls.filter(event => event[0].constructor.name === 'CustomEvent')[
    mockCallNumber
  ];
  expect(customEvents.length).toBe(1);
  expect(customEvents[0].type).toBe(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE);
  expect(customEvents[0].detail.code).toBe(attributeCode);
}

function buildSimpleProduct(): Product {
  return {
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
      variant_navigation: [],
      family_variant: {
        variant_attribute_sets: [],
      },
      parent_attributes: [],
    },
    created: null,
    updated: null,
  };
}

function buildThirdLevelProduct(): Product {
  return {
    categories: [],
    enabled: true,
    family: 'led_tvs',
    identifier: null,
    meta: {
      id: 3333,
      label: {},
      attributes_for_this_level: ['weight'],
      level: 2,
      model_type: 'product',
      variant_navigation: [
        {
          axes: {en_US: ''},
          selected: {id: 1111},
        },
        {
          axes: {en_US: ''},
          selected: {id: 2222},
        },
        {
          axes: {en_US: ''},
          selected: {id: 3333},
        },
      ],
      family_variant: {
        variant_attribute_sets: [
          {
            attributes: ['title'],
          },
          {
            attributes: ['weight'],
          },
        ],
      },
      parent_attributes: ['title'],
    },
    created: null,
    updated: null,
  };
}

function buildSecondLevelProduct(): Product {
  return {
    categories: [],
    enabled: true,
    family: 'led_tvs',
    identifier: null,
    meta: {
      id: 3333,
      label: {},
      attributes_for_this_level: ['title'],
      level: 1,
      model_type: 'product',
      variant_navigation: [
        {
          axes: {en_US: ''},
          selected: {id: 1111},
        },
        {
          axes: {en_US: ''},
          selected: {id: 2222},
        },
      ],
      family_variant: {
        variant_attribute_sets: [
          {
            attributes: ['title'],
          },
        ],
      },
      parent_attributes: ['description'],
    },
    created: null,
    updated: null,
  };
}

function renderWithData(product: Product, ui: React.ReactElement) {
  const initialState = {
    catalogContext: {channel: 'ecommerce', locale: 'en_US'},
    product: product,
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
          {
            code: 'weight',
            type: 'text',
            group: '',
            validation_rule: null,
            validation_regexp: null,
            wysiwyg_enabled: null,
            localizable: true,
            scopable: true,
            labels: {
              en_US: 'Weight',
            },
            is_read_only: true,
            meta: {id: 2},
          },
          {
            code: 'title',
            type: 'text',
            group: '',
            validation_rule: null,
            validation_regexp: null,
            wysiwyg_enabled: null,
            localizable: true,
            scopable: true,
            labels: {
              en_US: 'Title',
            },
            is_read_only: true,
            meta: {id: 3},
          },
        ],
        attribute_as_label: 'title',
        labels: {
          en_US: 'LED TVs',
        },
      },
    },
  };
  return renderWithProductEditFormContextHelper(ui, initialState);
}
