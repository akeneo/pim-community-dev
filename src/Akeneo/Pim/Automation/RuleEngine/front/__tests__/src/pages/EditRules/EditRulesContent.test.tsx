import React from 'react';
import {EditRulesContent} from '../../../../src/pages/EditRules/EditRulesContent';
import userEvent from '@testing-library/user-event';
import {wait} from '@testing-library/dom';
import {render, act, screen} from '../../../../test-utils';
import {IndexedScopes} from '../../../../src/repositories/ScopeRepository';
import {Security} from '../../../../src/dependenciesTools';
import {uiLocales} from '../../factories';

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
      enabled: true,
      actions: [],
      conditions: [],
      labels: {en_US: 'toto'},
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
        labels: {en_US: 'e-commerce'},
        meta: {},
      },
    };
    const security: Security = {isGranted: (_acl: string) => true};
    // When
    const {findByText, findByLabelText} = render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        setIsDirty={setIsDirty}
        security={security}
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
      enabled: true,
      actions: [],
      conditions: [],
      labels: {fr_FR: 'toto'},
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
        labels: {en_US: 'e-commerce'},
        meta: {},
      },
    };
    const security: Security = {isGranted: (_acl: string) => true};
    // When
    const {findByTestId, findByText, findByLabelText} = render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        setIsDirty={setIsDirty}
        security={security}
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

  it('does not display the "save and execute" button when user has not the permission', async () => {
    // Given
    const ruleDefinitionCode = 'my_code';
    const ruleDefinition = {
      id: 1,
      code: ruleDefinitionCode,
      type: 'product',
      priority: 0,
      enabled: true,
      actions: [],
      conditions: [],
      labels: {fr_FR: 'toto'},
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
        labels: {en_US: 'e-commerce'},
        meta: {},
      },
    };
    const security: Security = {isGranted: (_acl: string) => false};
    // When
    render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        setIsDirty={setIsDirty}
        security={security}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(
      screen.queryByText('pimee_catalog_rule.form.edit.execute.button')
    ).not.toBeInTheDocument();
  });

  it('displays or hides the "save and execute" button according to the status', async () => {
    // Given
    const ruleDefinitionCode = 'my_code';
    const ruleDefinition = {
      id: 1,
      code: ruleDefinitionCode,
      type: 'product',
      priority: 0,
      enabled: true,
      actions: [],
      conditions: [],
      labels: {fr_FR: 'toto'},
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
        labels: {en_US: 'e-commerce'},
        meta: {},
      },
    };
    const security: Security = {isGranted: (_acl: string) => true};
    // When
    render(
      <EditRulesContent
        ruleDefinitionCode={ruleDefinitionCode}
        ruleDefinition={ruleDefinition}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        setIsDirty={setIsDirty}
        security={security}
      />,
      {
        legacy: true,
      }
    );
    // Then
    expect(
      await screen.findByText('pimee_catalog_rule.form.edit.execute.button')
    ).toBeInTheDocument();
    // When
    await act(async () => {
      userEvent.click(await screen.findByTestId('edit-rules-input-status'));
    });
    // Then
    expect(
      screen.queryByText('pimee_catalog_rule.form.edit.execute.button')
    ).not.toBeInTheDocument();
  });
});
