import 'jest-fetch-mock';
import React from 'react';
import { act, renderWithProviders, screen } from '../../../../../../test-utils';
import userEvent from '@testing-library/user-event';
import { GroupsConditionLine } from '../../../../../../src/pages/EditRules/components/conditions/GroupsConditionLine';
import { Operator } from '../../../../../../src/models/Operator';
import { clearGroupRepositoryCache } from '../../../../../../src/repositories/GroupRepository';

jest.mock('../../../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../../../src/components/Select2Wrapper/Select2Wrapper');

describe('GroupsConditionLine', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    clearGroupRepositoryCache();
  });
  it('should display a groups condition line and be able to update it', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pim_enrich_group_rest_search?%7B%22identifiers%22:%22winter,tshirts%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify({
            results: [
              { id: 'tshirts', text: 'T-shirts' },
              { id: 'winter', text: 'Winter' },
            ],
          })
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    const defaultValues = {
      content: {
        conditions: [
          {},
          {
            field: 'groups',
            operator: Operator.NOT_IN_LIST,
            value: ['winter', 'tshirts'],
          },
        ],
      },
    };
    const toRegister = [
      { name: 'content.conditions[1].field', type: 'custom' },
      { name: 'content.conditions[1].operator', type: 'custom' },
      { name: 'content.conditions[1].value', type: 'custom' },
    ];

    renderWithProviders(
      <GroupsConditionLine
        lineNumber={1}
        locales={[]}
        scopes={{}}
        currentCatalogLocale={'fr_FR'}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    expect(
      screen.getByText('pimee_catalog_rule.form.edit.fields.groups')
    ).toBeInTheDocument();
    const operatorSelector = screen.getByTestId('edit-rules-input-1-operator');
    const valueSelector = screen.queryByTestId('edit-rules-input-1-value');

    expect(operatorSelector).toBeInTheDocument();
    expect(operatorSelector).toHaveValue(Operator.NOT_IN_LIST);
    expect(valueSelector).toBeInTheDocument();
    expect(valueSelector).toHaveValue(['winter', 'tshirts']);

    await act(async () => {
      userEvent.selectOptions(operatorSelector, Operator.IS_NOT_EMPTY);
      expect(await valueSelector).not.toBeInTheDocument();
    });
  });
});
