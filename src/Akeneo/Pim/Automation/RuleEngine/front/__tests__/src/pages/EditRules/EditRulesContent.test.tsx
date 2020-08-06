import React from 'react';
import { EditRulesContent } from '../../../../src/pages/EditRules/EditRulesContent';
import userEvent from '@testing-library/user-event';
import { wait } from '@testing-library/dom';
import { render, act } from '../../../../test-utils';
import { IndexedScopes } from '../../../../src/repositories/ScopeRepository';

const setIsDirty = (_isDirty: boolean) => {};

describe('EditRulesContent', () => {
  it('should display an unsaved changes alert after user have changed an input', async () => {
    // Given
    const ruleDefinitionCode = 'my_code';
    const ruleDefinition = {
      id: 1,
      code: ruleDefinitionCode,
      type: 'product',
      priority: 0,
      actions: [],
      conditions: [],
      labels: { en_US: 'toto' },
    };
    const locales = [
      {
        code: 'en_US',
        label: 'English (United States)',
        region: 'United States',
        language: 'English',
      },
    ];
    const scopes: IndexedScopes = {
      ecommerce: {
        code: 'ecommerce',
        currencies: ['EUR', 'USD'],
        locales: [locales[0]],
        category_tree: 'master',
        conversion_units: [],
        labels: { en_US: 'e-commerce' },
        meta: {},
      },
    };
    // When
    const { findByText, findByLabelText } = render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        scopes={scopes}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );
    const propertiesTab = (await findByText(
      'pim_common.properties'
    )) as HTMLButtonElement;
    act(() => userEvent.click(propertiesTab));
    const inputPriority = (await findByLabelText(
      'pimee_catalog_rule.form.edit.priority.label'
    )) as HTMLInputElement;
    await act(() => userEvent.type(inputPriority, '1'));
    // Then
    expect(await findByText('There are unsaved changes.')).toBeInTheDocument();
  });

  it('updates the title when the rule is saved', async () => {
    // Given
    const ruleDefinitionCode = 'my_code';
    const ruleDefinition = {
      id: 1,
      code: ruleDefinitionCode,
      type: 'product',
      priority: 0,
      actions: [],
      conditions: [],
      labels: { fr_FR: 'toto' },
    };
    const locales = [
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
        locales: [locales[0]],
        category_tree: 'master',
        conversion_units: [],
        labels: { en_US: 'e-commerce' },
        meta: {},
      },
    };
    // When
    const { findByTestId, findByText, findByLabelText } = render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        scopes={scopes}
        setIsDirty={setIsDirty}
      />,
      {
        legacy: true,
      }
    );

    const titleDiv = await findByTestId('rule-title');
    expect(titleDiv).toBeInTheDocument();
    expect(titleDiv).toHaveTextContent('[my_code]');

    // When
    const propertiesTab = (await findByText(
      'pim_common.properties'
    )) as HTMLButtonElement;
    act(() => userEvent.click(propertiesTab));
    const usLabelInput = (await findByLabelText(
      'English (United States)'
    )) as HTMLInputElement;
    await act(() => userEvent.type(usLabelInput, 'The new label'));
    // Then
    await wait(() => expect(titleDiv).toHaveTextContent('The new label'));
  });
});
