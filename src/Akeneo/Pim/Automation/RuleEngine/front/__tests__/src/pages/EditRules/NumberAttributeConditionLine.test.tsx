import React from 'react';
import { renderWithProviders } from '../../../../test-utils';
import 'jest-fetch-mock';
import { NumberAttributeConditionLine } from '../../../../src/pages/EditRules/components/conditions/NumberAttributeConditionLine';
import { Operator } from '../../../../src/models/Operator';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';
import { createAttribute, locales, scopes } from '../../factories';

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../src/fetch/categoryTree.fetcher.ts');

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

    const { findByText, findByTestId } = renderWithProviders(
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
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true }
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue('!=');
    expect(await findByTestId('edit-rules-input-1-scope')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-scope')).toHaveValue(
      'mobile'
    );
    expect(await findByTestId('edit-rules-input-1-locale')).toBeInTheDocument();
    expect(await findByTestId('edit-rules-input-1-locale')).toHaveValue(
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

    const { findByText, findByTestId, queryByTestId } = renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'conditionWithNonLocalizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
          value: '10',
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true }
    );

    expect(await findByText('Nom')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();

    expect(queryByTestId('edit-rules-input-1-scope')).toBeNull();
    expect(queryByTestId('edit-rules-input-1-locale')).toBeNull();
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

    const { findByText, findByTestId, queryByTestId } = renderWithProviders(
      <NumberAttributeConditionLine
        condition={{
          field: 'localizableScopableAttribute',
          operator: Operator.NOT_EQUAL,
          value: '10',
        }}
        lineNumber={1}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
      />,
      { all: true }
    );
    expect(await findByText('Name')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
    await wait(() =>
      expect(queryByTestId('edit-rules-input-1-value')).toBeNull()
    );

    userEvent.selectOptions(operatorSelector, Operator.NOT_EQUAL);
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();
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

    const {
      findByText,
      findByTestId,
      queryByTestId,
      queryByText,
    } = renderWithProviders(
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
      { all: true }
    );
    expect(await findByText('Name')).toBeInTheDocument();
    const operatorSelector = await findByTestId('edit-rules-input-1-operator');
    expect(operatorSelector).toBeInTheDocument();
    expect(queryByTestId('edit-rules-input-1-value')).toBeDefined();

    userEvent.selectOptions(
      await findByTestId('edit-rules-input-1-scope'),
      'ecommerce'
    );
    expect(queryByText('German')).toBeInTheDocument();
    expect(queryByText('French')).toBeInTheDocument();
    expect(queryByText('English')).toBeInTheDocument();
    userEvent.selectOptions(
      await findByTestId('edit-rules-input-1-scope'),
      'mobile'
    );
    expect(queryByText('German')).toBeInTheDocument();
    expect(queryByText('French')).not.toBeInTheDocument();
    expect(queryByText('English')).toBeInTheDocument();
  });
});
