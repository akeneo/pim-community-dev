import { renderWithProviders } from '../../../../test-utils';
import React from 'react';
import { TextAttributeConditionLine } from '../../../../src/pages/EditRules/components/conditions/TextAttributeConditionLine';
import { TextAttributeCondition } from '../../../../src/models/conditions';
import { Operator } from '../../../../src/models/Operator';
import { Router } from '../../../../src/dependenciesTools';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';
import { createAttribute, locales, scopes } from '../../factories';

const conditionWithLocalizableScopableAttribute: TextAttributeCondition = {
  scope: 'mobile',
  module: TextAttributeConditionLine,
  locale: 'en_US',
  attribute: createAttribute({}),
  field: 'name',
  operator: Operator.NOT_EQUAL,
  value: 'Canon',
};

const conditionWithNonLocalizableScopableAttribute: TextAttributeCondition = {
  module: TextAttributeConditionLine,
  attribute: createAttribute({ localizable: false, scopable: false }),
  field: 'name',
  operator: Operator.NOT_EQUAL,
  value: 'Canon',
};

const defaultCondition: TextAttributeCondition = {
  module: TextAttributeConditionLine,
  attribute: createAttribute({ localizable: true, scopable: true }),
  field: 'name',
  operator: Operator.IS_EMPTY,
};

const translate = jest.fn((key: string) => key);
const router: Router = {
  generate: jest.fn(),
  redirect: jest.fn(),
};

jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/fetch/categoryTree.fetcher.ts');

describe('TextAttributeConditionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should display the text attribute conditionWithLocalizableScopableAttribute with locale and scope selectors', async () => {
    const {
      findByText,
      findByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
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

  it('should display the text attribute conditionWithLocalizableScopableAttribute without locale and scope selectors', async () => {
    const {
      findByText,
      findByTestId,
      queryByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithNonLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        router={router}
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
    // Given
    const {
      findByText,
      findByTestId,
      queryByTestId,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={conditionWithLocalizableScopableAttribute}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        router={router}
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
    // Given
    const {
      findByText,
      findByTestId,
      queryByTestId,
      queryByText,
    } = renderWithProviders(
      <TextAttributeConditionLine
        condition={defaultCondition}
        lineNumber={1}
        translate={translate}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        router={router}
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
