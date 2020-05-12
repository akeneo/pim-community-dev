import React from 'react';
import { EditRulesContent } from '../../../../src/pages/EditRules/EditRulesContent';
import userEvent from '@testing-library/user-event';
import { render, act } from '../../../../test-utils';
import { IndexedScopes } from '../../../../src/repositories/ScopeRepository';

jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');

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
});
