import { renderWithProviders, act, waitFor } from '../../../../test-utils';
import React from 'react';
import 'jest-fetch-mock';
import { ClearAttributeAction } from '../../../../src/models/actions';
import { ClearAttributeActionLine } from '../../../../src/pages/EditRules/components/actions/ClearAttributeActionLine';
import { IndexedScopes } from '../../../../src/repositories/ScopeRepository';
import { Router } from '../../../../src/dependenciesTools';
import { Attribute } from '../../../../src/models';
import { clearCache } from '../../../../src/repositories/AttributeRepository';
import userEvent from '@testing-library/user-event';

const router: Router = {
  'generate': jest.fn(),
  'redirect': jest.fn(),
};

const translate = jest.fn((key: string) => key);
const action: ClearAttributeAction = {
  module: ClearAttributeActionLine,
  type: 'clear',
  field: 'name',
}

const locales = [
  {
    code: 'de_DE',
    label: 'German (Germany)',
    region: 'Germany',
    language: 'German',
  },
  {
    code: 'en_US',
    label: 'English (United States)',
    region: 'United States',
    language: 'English',
  },
  {
    code: 'fr_FR',
    label: 'French (France)',
    region: 'France',
    language: 'French',
  },
];
const scopes: IndexedScopes = {
  ecommerce: {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales: locales,
    category_tree: 'master',
    conversion_units: [],
    labels: { en_US: 'e-commerce' },
    meta: {},
  },
  mobile: {
    code: 'mobile',
    currencies: ['EUR', 'USD'],
    locales: [locales[0], locales[1]],
    category_tree: 'master',
    conversion_units: [],
    labels: { en_US: 'Mobile' },
    meta: {},
  },
};

const createAttribute = (data: { [key: string]: any }): Attribute => {
  return {
    code: 'name',
    type: 'pim_catalog_text',
    group: 'marketing',
    unique: false,
    useable_as_grid_filter: true,
    allowed_extensions: [],
    metric_family: null,
    default_metric_unit: null,
    reference_data_name: null,
    available_locales: [],
    max_characters: null,
    validation_rule: null,
    validation_regexp: null,
    wysiwyg_enabled: null,
    number_min: null,
    number_max: null,
    decimals_allowed: null,
    negative_allowed: null,
    date_min: null,
    date_max: null,
    max_file_size: null,
    minimum_input_length: null,
    sort_order: 1,
    localizable: true,
    scopable: true,
    labels: {en_US: 'Name', fr_FR: 'Nom'},
    auto_option_sorting: null,
    is_read_only: false,
    empty_value: null,
    field_type: 'text',
    filter_types: {},
    is_locale_specific: false,
    meta: {
      id: 42
    },
    ...data
  };
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');

describe('ClearAttributeActionLine', () => {
  beforeEach(() => {
    clearCache();
    fetchMock.resetMocks();
  })

  it('should display the clear attribute action line without locale or scope', async () => {
    fetchMock.mockResponses([
      JSON.stringify(createAttribute({
        scopable: false,
        localizable: false,
      })),
      { status: 200 },
    ]);

    const { findByText, queryByText, findByTestId } = renderWithProviders(
      <ClearAttributeActionLine
        translate={translate}
        router={router}
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        action={action}
        handleDelete={()=>{}}
        locales={locales}
        scopes={scopes}
      />, { all: true }
    );

    expect(await findByText('pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(queryByText('Channel pim_common.required_label')).not.toBeInTheDocument();
    expect(queryByText('Locale pim_common.required_label')).not.toBeInTheDocument();
  });

  it ('should display the clear attribute action line with locale and scope', async () => {
    fetchMock.mockResponse(() => {
      return Promise.resolve(JSON.stringify(createAttribute({})));
    });

    const { findByText, findByTestId, queryByText } = renderWithProviders(
      <ClearAttributeActionLine
        translate={translate}
        router={router}
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        action={action}
        handleDelete={()=>{}}
        locales={locales}
        scopes={scopes}
      />, { all: true }
    );

    expect(await findByText('pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-action-1-field')).toHaveValue('name');
    expect(queryByText('Locale pim_common.required_label')).toBeInTheDocument();
    expect(queryByText('Channel pim_common.required_label')).toBeInTheDocument();
  });

  it('should remove/add the scope and label when switching from a scopable attribute to a non-scopable one', async () => {
    fetchMock.mockResponse((request: Request) => {
      // attribute values
      if (request.url.includes('pim_enrich_attribute_rest_get')) {  
        if (request.url.includes('name')) {
          return Promise.resolve(JSON.stringify(createAttribute({
            code: 'name',
            scopable: true,
            localizable: true,
          })));
        }

        if (request.url.includes('description')) {
          return Promise.resolve(JSON.stringify(createAttribute({
            code: 'description',
            scopable: false,
            localizable: false,
          })));
        }
      }
      // attributes available for the rule
      if (request.url.includes('pimee_enrich_rule_definition_get_available_fields')) {
        return Promise.resolve(JSON.stringify([
          {
            id: 'groupId',
            text: 'groupLabel',
            children: [
              {
                id: 'name',
                text: 'name',
              },
              {
                id: 'description',
                text: 'description',
              },
            ],
          }
        ]));
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const { findByTestId, findByText, queryByText } = renderWithProviders(
      <ClearAttributeActionLine
        translate={translate}
        router={router}
        currentCatalogLocale={'en-US'}
        lineNumber={1}
        action={action}
        handleDelete={()=>{}}
        locales={locales}
        scopes={scopes}
      />, { all: true }
    );

    const attributeSelector = await findByTestId('edit-rules-action-1-field');
    expect(attributeSelector).toBeInTheDocument();
    expect(attributeSelector).toHaveValue('name');
    expect(await findByText('Locale pim_common.required_label')).toBeInTheDocument();
    expect(await findByText('Channel pim_common.required_label')).toBeInTheDocument();

    act(() => {
      userEvent.selectOptions(attributeSelector, 'description');
    });

    await waitFor(() => expect(queryByText('Channel pim_common.required_label')).not.toBeInTheDocument());
    await waitFor(() => expect(queryByText('Locale pim_common.required_label')).not.toBeInTheDocument());

    act(() => {
      userEvent.selectOptions(attributeSelector, 'name');
    });

    await wait(() => {
      expect(attributeSelector).toHaveValue('name');
      expect(queryByText('Channel pim_common.required_label')).toBeInTheDocument();
      expect(queryByText('Locale pim_common.required_label')).toBeInTheDocument();
    });
  });
});
