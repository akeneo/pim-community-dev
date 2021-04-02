import React from 'react';
import 'jest-fetch-mock';
import {renderWithProviders, screen} from '../../../../../../test-utils';
import {locales, scopes, uiLocales} from '../../../../factories';
import {AddGroupsActionLine} from '../../../../../../src/pages/EditRules/components/actions/AddGroupsActionLine';
import {clearGroupRepositoryCache} from '../../../../../../src/repositories/GroupRepository';

describe('AddGroupsActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearGroupRepositoryCache();
  });

  it('should be able to display a new add groups action', async () => {
    renderWithProviders(
      <AddGroupsActionLine
        lineNumber={0}
        locales={locales}
        uiLocales={uiLocales}
        scopes={scopes}
        currentCatalogLocale={'en_US'}
        handleDelete={jest.fn()}
      />,
      {all: true}
    );
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.add_groups.title'
      )
    ).toBeInTheDocument();

    const select = screen.getByTestId('edit-rules-actions-0-items');
    expect(select).toHaveValue([]);
  });

  it('should be able to display an existing add groups action', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pim_enrich_group_rest_search?%7B%22identifiers%22:%22winter,tshirts%22%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify({
            results: [
              {id: 'tshirts', text: 'T-shirts'},
              {id: 'winter', text: 'Winter'},
            ],
          })
        );
      }

      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const defaultValues = {
      content: {
        actions: [
          {
            type: 'add',
            field: 'groups',
            items: ['winter', 'tshirts'],
          },
        ],
      },
    };
    const toRegister = [
      {name: 'content.actions[0].type', type: 'custom'},
      {name: 'content.actions[0].field', type: 'custom'},
      {name: 'content.actions[0].items', type: 'custom'},
    ];

    renderWithProviders(
      <AddGroupsActionLine
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
    expect(
      await screen.findByText(
        'pimee_catalog_rule.form.edit.actions.add_groups.title'
      )
    ).toBeInTheDocument();
    expect(screen.getByTestId('edit-rules-actions-0-items')).toHaveValue([
      'tshirts',
      'winter',
    ]);
  });
});
