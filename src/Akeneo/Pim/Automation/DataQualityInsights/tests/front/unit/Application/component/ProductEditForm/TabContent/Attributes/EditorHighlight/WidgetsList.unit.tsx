import React from 'react';
import {Provider} from 'react-redux';

import {render} from '@testing-library/react';

import WidgetsList from '@akeneo-pim-ee/data-quality-insights/src/application/component/ProductEditForm/TabContent/Attributes/EditorHighlight/WidgetsList';
import {
  createStoreWithInitialState,
  ProductEditFormState,
} from '@akeneo-pim-ee/data-quality-insights/src/infrastructure/store/productEditFormStore';
import {createWidget} from '@akeneo-pim-ee/data-quality-insights/src/application/helper';

beforeEach(() => {
  jest.resetModules();
});

describe('WidgetsList', () => {
  test('build widget list when any suggested title has not been found yet', async () => {
    const editor1 = document.createElement('textarea');
    const editor2 = document.createElement('input');
    editor2.setAttribute('type', 'text');
    const editor3 = document.createElement('input');
    editor3.setAttribute('type', 'text');
    const editor4 = document.createElement('div');
    editor4.setAttribute('contenteditable', 'true');

    const {getAllByTestId} = renderComponent({
      editorHighlight: {
        widgets: {
          'spellcheck-1': createWidget('spellcheck-1', editor1, 'attribute_textarea_editor_id', 'attribute_textarea'),
          'spellcheck-2': createWidget('spellcheck-2', editor2, 'attribute_text_editor_id', 'attribute_text'),
          'spellcheck-3': createWidget(
            'spellcheck-3',
            editor4,
            'attribute_wysiwyg_text_editor_id',
            'attribute_wysiwyg_text'
          ),
        },
      },
    });

    expect(getAllByTestId(/^editor-highlight-spellcheck-.+/i).length).toBe(3);
  });
});

const renderComponent = (testedState: any) => {
  const state = {
    ...initialProductEditFormState,
    ...testedState,
  };

  return render(
    <Provider store={createStoreWithInitialState(state)}>
      <WidgetsList />
    </Provider>
  );
};

const initialProductEditFormState: ProductEditFormState = {
  catalogContext: {
    channel: '',
    locale: '',
  },
  pageContext: {
    currentTab: '',
    attributesTabIsLoading: false,
    attributeToImprove: null,
  },
  productEvaluation: {},
  families: {},
  product: {
    categories: [],
    enabled: true,
    family: null,
    identifier: null,
    meta: {
      id: null,
      label: {},
      level: null,
      attributes_for_this_level: [],
      model_type: 'product',
      variant_navigation: [],
      family_variant: {
        variant_attribute_sets: [],
      },
      parent_attributes: [],
    },
    created: null,
    updated: null,
  },
  editorHighlight: {
    widgets: {},
    popover: {
      isOpen: false,
      highlight: null,
      widgetId: null,
      handleOpening: () => {},
      handleClosing: () => {},
    },
  },
};
