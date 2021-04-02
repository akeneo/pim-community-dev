import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  fireEvent,
  renderWithProviders,
  screen,
} from '../../../../../../test-utils';
import {
  attributeSelect2Response,
  createAttribute,
  locales,
  scopes,
  measurementFamiliesResponse,
  currencies,
  uiLocales,
} from '../../../../factories';
import userEvent from '@testing-library/user-event';
import {clearMeasurementFamilyRepositoryCache} from '../../../../../../src/repositories/MeasurementFamilyRepository';
import {CalculateActionLine} from '../../../../../../src/pages/EditRules/components/actions/CalculateActionLine';
import {AttributeType} from '../../../../../../src/models';
import {clearAttributeRepositoryCache} from '../../../../../../src/repositories/AttributeRepository';
import {clearCurrencyRepositoryCache} from '../../../../../../src/repositories/CurrencyRepository';

jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/fetch/categoryTree.fetcher.ts');

const buildDefaultValues = (data: {[key: string]: any}) => {
  return {
    content: {
      actions: [
        {
          type: 'calculate',
          destination: {
            field: 'weight',
            unit: 'KILOGRAM',
            locale: 'en_US',
            scope: 'mobile',
          },
          round_precision: 2,
          source: {},
          operation_list: [],
          ...data,
        },
      ],
    },
  };
};

const toRegister = [
  {name: 'content.actions[0].destination.field', type: 'custom'},
  {name: 'content.actions[0].destination.unit', type: 'custom'},
  {name: 'content.actions[0].destination.locale', type: 'custom'},
  {name: 'content.actions[0].destination.scope', type: 'custom'},
  {
    name: 'content.actions[0].round_precision',
    type: 'custom',
  },
  {name: 'content.actions[0].operation_list', type: 'custom'},
  {name: 'content.actions[0].source', type: 'custom'},
];

const assertTextIsInDocument = async (text: string): Promise<void> => {
  expect(await screen.findByText(text)).toBeInTheDocument();
};
const assertTextIsNotInDocument = (text: string): void => {
  expect(screen.queryByText(text)).not.toBeInTheDocument();
};
const assertTestIdIsNotInDocument = (testId: string): void => {
  expect(screen.queryByTestId(testId)).not.toBeInTheDocument();
};
const assertTestIdIsInDocument = (testId: string): void => {
  expect(screen.queryByTestId(testId)).toBeInTheDocument();
};
const assertTestIdHasValue = (testId: string, value: any): void => {
  expect(screen.getByTestId(testId)).toHaveValue(value);
};

const weightAttribute = createAttribute({
  code: 'weight',
  type: AttributeType.METRIC,
  decimals_allowed: true,
  metric_family: 'weight_metric_family',
  localizable: true,
  scopable: true,
});

const marginAttribute = createAttribute({
  code: 'margin',
  type: AttributeType.NUMBER,
  decimals_allowed: false,
  metric_family: null,
  localizable: false,
  scopable: false,
});

const sellPriceAttribute = createAttribute({
  code: 'sellprice',
  type: AttributeType.PRICE_COLLECTION,
  decimals_allowed: true,
  metric_family: null,
  localizable: false,
  scopable: false,
});

const response = (request: Request) => {
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('weight')
  ) {
    return Promise.resolve(JSON.stringify(weightAttribute));
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('margin')
  ) {
    return Promise.resolve(JSON.stringify(marginAttribute));
  }
  if (
    request.url.includes('pim_enrich_attribute_rest_get') &&
    request.url.includes('sellprice')
  ) {
    return Promise.resolve(JSON.stringify(sellPriceAttribute));
  }
  if (
    request.url.includes('pimee_enrich_rule_definition_get_available_fields')
  ) {
    return Promise.resolve(JSON.stringify(attributeSelect2Response));
  }
  if (request.url.includes('pim_enrich_currency_rest_index')) {
    return Promise.resolve(JSON.stringify(currencies));
  }
  if (request.url.includes('pim_enrich_measures_rest_index')) {
    return Promise.resolve(JSON.stringify(measurementFamiliesResponse));
  }
  throw new Error(`The "${request.url}" url is not mocked.`);
};

describe('CalculateActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearMeasurementFamilyRepositoryCache();
    clearAttributeRepositoryCache();
    clearCurrencyRepositoryCache();
  });

  it('should display the calculate action line with a metric target attribute', async () => {
    const defaultValues = buildDefaultValues({});

    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.select_target'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.round_precision'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.measurement_unit'
    );
    await assertTextIsInDocument(
      'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
    );
    await assertTextIsInDocument(
      'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
    );

    assertTestIdHasValue('edit-rules-action-0-destination-field', 'weight');
    assertTestIdHasValue('edit-rules-action-0-destination-unit', 'KILOGRAM');
    assertTestIdHasValue('edit-rules-action-0-destination-locale', 'en_US');
    assertTestIdHasValue('edit-rules-action-0-destination-scope', 'mobile');
    assertTestIdHasValue('edit-rules-action-0-round-precision', 2);
  });

  it('should display the calculate action line with a number target attribute', async () => {
    const defaultValues = buildDefaultValues({
      destination: {
        field: 'margin',
      },
      round_precision: undefined,
    });

    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.select_target'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.round_precision'
    );
    assertTextIsNotInDocument(
      'pimee_catalog_rule.form.edit.fields.measurement_unit'
    );
    assertTextIsNotInDocument(
      'pim_enrich.entity.channel.uppercase_label pim_common.required_label'
    );
    assertTextIsNotInDocument(
      'pim_enrich.entity.locale.uppercase_label pim_common.required_label'
    );

    assertTestIdHasValue('edit-rules-action-0-destination-field', 'margin');
    assertTestIdHasValue('edit-rules-action-0-round-precision', null);
    assertTestIdIsNotInDocument('edit-rules-action-0-destination-unit');
  });

  it('should display the calculate action line with a price target attribute', async () => {
    const defaultValues = buildDefaultValues({
      destination: {
        field: 'sellprice',
        currency: 'USD',
      },
    });

    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.select_target'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.currency pim_common.required_label'
    );

    assertTestIdHasValue('edit-rules-action-0-destination-field', 'sellprice');
    assertTestIdHasValue('edit-rules-action-0-destination-currency', 'USD');
    assertTestIdIsNotInDocument('edit-rules-action-0-destination-unit');
  });

  it('should be possible to change the target attribute and its options', async () => {
    const defaultValues = buildDefaultValues({
      destination: {
        field: 'margin',
      },
      round_precision: undefined,
    });

    fetchMock.mockResponse(response);

    renderWithProviders(
      <CalculateActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      {all: true},
      {defaultValues, toRegister}
    );

    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.attribute pim_common.required_label'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.round_precision'
    );
    assertTestIdHasValue('edit-rules-action-0-destination-field', 'margin');
    assertTestIdIsNotInDocument('edit-rules-action-0-destination-unit');
    assertTestIdIsNotInDocument('edit-rules-action-0-destination-locale');
    assertTestIdIsNotInDocument('edit-rules-action-0-destination-scope');

    await act(async () => {
      userEvent.click(
        await screen.findByTestId('edit-rules-action-0-destination-field')
      );
      expect(
        (await screen.findByTestId('edit-rules-action-0-destination-field'))
          .children.length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('edit-rules-action-0-destination-field'),
        {
          target: {value: 'weight'},
        }
      );
    });
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.fields.measurement_unit'
    );
    await assertTextIsInDocument(
      'pimee_catalog_rule.form.edit.actions.calculate.select_target'
    );
    assertTestIdHasValue('edit-rules-action-0-destination-field', 'weight');
    assertTestIdIsInDocument('edit-rules-action-0-destination-unit');
    assertTestIdIsInDocument('edit-rules-action-0-destination-unit');
    assertTestIdIsInDocument('edit-rules-action-0-destination-locale');
    assertTestIdIsInDocument('edit-rules-action-0-destination-scope');
    await act(async () => {
      userEvent.click(
        await screen.findByTestId('edit-rules-action-0-destination-unit')
      );
      expect(
        (await screen.findByTestId('edit-rules-action-0-destination-unit'))
          .children.length
      ).toBeGreaterThan(1);
      fireEvent.change(
        await screen.findByTestId('edit-rules-action-0-destination-unit'),
        {
          target: {value: 'KILOGRAM'},
        }
      );
    });
    assertTestIdHasValue('edit-rules-action-0-destination-unit', 'KILOGRAM');
  });
});
