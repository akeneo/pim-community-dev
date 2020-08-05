import React from 'react';
import { renderWithProviders, screen } from '../../../../test-utils';
import 'jest-fetch-mock';
import { NumberAttributeConditionLine } from '../../../../src/pages/EditRules/components/conditions/NumberAttributeConditionLine';
import { Operator } from '../../../../src/models/Operator';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';
import { createAttribute, locales, scopes } from '../../factories';

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../src/fetch/categoryTree.fetcher.ts');
jest.mock('../../../../src/dependenciesTools/AssetManager/AssetSelector');
jest.mock(
  '../../../../src/dependenciesTools/components/ReferenceEntity/ReferenceEntitySelector'
);

describe('NumberAttributeConditionLine', () => {
  afterEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the locale and scope selectors', async () => {
    fetchMock.mockResponses([
      JSON.stringify(
        createAttribute({
          type: 'pim_catalog_number',
          localizable: true,
          scopable: true,
        })
      ),
      { status: 200 },
    ]);
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'localizableScopableAttribute',
            operator: Operator.NOT_EQUAL,
            value: '10',
            scope: 'mobile',
            locale: 'en_US',
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
      { name: 'content.conditions[1].locale', type: 'custom' },
      { name: 'content.conditions[1].scope', type: 'custom' },
    ];

    renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'localizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('Nom')).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('!=');
    expect(screen.getByTestId('edit-rules-input-1-scope')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-scope')).toHaveValue(
      'mobile'
    );
    expect(screen.getByTestId('edit-rules-input-1-locale')).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-locale')).toHaveValue(
      'en_US'
    );
  });

  it('should not display the locale and scope selectors', async () => {
    fetchMock.mockResponses([
      JSON.stringify(
        createAttribute({
          type: 'pim_catalog_number',
          localizable: false,
          scopable: false,
        })
      ),
      { status: 200 },
    ]);
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'conditionWithNonLocalizableScopableAttribute',
            operator: Operator.NOT_EQUAL,
            value: '10',
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
    ];

    renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'conditionWithNonLocalizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(await screen.findByText('Nom')).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(screen.queryByTestId('edit-rules-input-1-scope')).toBeNull();
    expect(screen.queryByTestId('edit-rules-input-1-locale')).toBeNull();
  });

  it('handles values option appearance based on selected operator', async () => {
    fetchMock.mockResponses([
      JSON.stringify(
        createAttribute({
          type: 'pim_catalog_number',
          localizable: false,
          scopable: false,
        })
      ),
      { status: 200 },
    ]);

    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'localizableScopableAttribute',
            operator: Operator.NOT_EQUAL,
            value: '10',
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
    ];

    renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'localizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(await screen.findByText('Name')).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    await wait(() =>
      expect(screen.queryByTestId('edit-rules-input-1-value')).toBeNull()
    );

    userEvent.selectOptions(operatorSelector, Operator.NOT_EQUAL);
    expect(screen.queryByTestId('edit-rules-input-1-value')).toBeDefined();
  });

  it('displays the matching locales regarding the scope', async () => {
    fetchMock.mockResponses([
      JSON.stringify(
        createAttribute({
          type: 'pim_catalog_number',
          localizable: true,
          scopable: true,
        })
      ),
      { status: 200 },
    ]);
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'localizableScopableAttribute',
            operator: Operator.NOT_EQUAL,
            value: '10',
            scope: 'mobile',
            locale: 'en_US',
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
      { name: 'content.conditions[1].scope', type: 'custom' },
      { name: 'content.conditions[1].locale', type: 'custom' },
    ];

    renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'localizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
          value: '10',
          scope: 'mobile',
          locale: 'en_US',
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    expect(await screen.findByText('Name')).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(
      screen.getByTestId('edit-rules-input-1-scope'),
      'ecommerce'
    );
    expect(screen.getByText('German')).toBeInTheDocument();
    expect(screen.getByText('French')).toBeInTheDocument();
    expect(screen.getByText('English')).toBeInTheDocument();
    userEvent.selectOptions(
      screen.getByTestId('edit-rules-input-1-scope'),
      'mobile'
    );
    expect(screen.getByText('German')).toBeInTheDocument();
    expect(screen.queryByText('French')).not.toBeInTheDocument();
    expect(screen.getByText('English')).toBeInTheDocument();
  });
});
