import React from 'react';
import 'jest-fetch-mock';
import { EditRules } from '../../../../src/pages/EditRules/';
import userEvent from '@testing-library/user-event';
import { act, render, fireEvent } from '../../../../test-utils';
import { Scope } from '../../../../src/models';

jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');

const ruleDefinitionCode = 'my_code';

const ruleDefinitionPayload = {
  id: 14,
  code: ruleDefinitionCode,
  type: 'product',
  priority: 0,
  content: { actions: [], conditions: [] },
  labels: {
    'en_US': 'My code'
  },
};

const localesPayload = [
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

const scopesPayload: Scope[] = [
  {
    code: 'ecommerce',
    currencies: ['EUR', 'USD'],
    locales: localesPayload,
    category_tree: 'master',
    conversion_units: [],
    labels: {
      'en_US': 'e-commerce'
    },
    meta: {},
  },
];

const setIsDirty = (_isDirty: boolean) => {};

describe('EditRules', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
  });

  it('should submit the form with the input data from rule properties', async () => {
    // Given
    fetchMock.mockResponses(
      [JSON.stringify(ruleDefinitionPayload), { status: 200 }],
      [JSON.stringify(localesPayload), { status: 200 }],
      [JSON.stringify(scopesPayload), { status: 200 }]
    );
    fetchMock.mockResponse(() => {
      return new Promise(resolve =>
        setTimeout(() => resolve({ body: 'ok' }), 1000)
      );
    });
    // When
    const { getByTestId, findByTestId, findByText, findByLabelText } = render(
      <EditRules ruleDefinitionCode={ruleDefinitionCode} setIsDirty={setIsDirty}/>,
      {
        legacy: true,
      }
    );
    const propertiesTab = (await findByText(
      'pim_common.properties'
    )) as HTMLButtonElement;
    userEvent.click(propertiesTab);
    const inputPriority = (await findByLabelText(
      'pimee_catalog_rule.form.edit.priority.label'
    )) as HTMLInputElement;
    const inputLabelUS = (await findByLabelText(
      'English (United States)'
    )) as HTMLInputElement;
    const inputLabelFrench = (await findByLabelText(
      'French (France)'
    )) as HTMLInputElement;
    const inputLabelGerman = (await findByLabelText(
      'German (Germany)'
    )) as HTMLInputElement;
    act(() => {
      userEvent.type(inputPriority, '1');
      userEvent.type(inputLabelUS, 'Hello');
      userEvent.type(inputLabelFrench, 'Salut');
      userEvent.type(inputLabelGerman, 'Hallo');
      fireEvent.submit(getByTestId('edit-rules-form'));
    });
    // Then
    expect(await findByTestId('akeneo-spinner')).toBeInTheDocument();
  });

  it('should render the page with the right title and right labels', async () => {
    // Given
    fetchMock.mockResponses(
      [JSON.stringify(ruleDefinitionPayload), { status: 200 }],
      [JSON.stringify(localesPayload), { status: 200 }],
      [JSON.stringify(scopesPayload), { status: 200 }]
    );
    // When
    const { findByText, findByLabelText } = render(
      <EditRules ruleDefinitionCode={ruleDefinitionCode} setIsDirty={setIsDirty}/>,
      {
        legacy: true,
      }
    );
    // Then
    expect(await findByText('My code')).toBeInTheDocument();
    expect(await findByLabelText('French (France)')).toBeInTheDocument();
    expect(await findByLabelText('English (United States)')).toBeInTheDocument();
  });
  it('should render an error', async () => {
    // Given
    fetchMock.mockResponses(
      [JSON.stringify({}), { status: 404 }],
      [JSON.stringify(localesPayload), { status: 200 }],
      [JSON.stringify(scopesPayload), { status: 200 }]
    )
    // When
    const { findByText } = render(
      <EditRules ruleDefinitionCode="inexisting_rule" setIsDirty={setIsDirty}/>,
      {
        legacy: true
      }
    );
    // Then
    expect(await findByText('There was an error (TODO: better display)')).toBeInTheDocument();
  });
});
