import React from 'react';
import 'jest-fetch-mock';
import {
  act,
  screen,
  renderWithProviders,
  waitForElementToBeRemoved,
} from '../../../../test-utils';
import { SetCategoriesAction } from '../../../../src/models/actions';
import { locales, scopes } from '../../factories';
import { SetCategoriesActionLine } from '../../../../src/pages/EditRules/components/actions/SetCategoriesActionLine';
import { CategoryCode } from '../../../../src/models';
import { createCategory } from '../../factories/CategoryFactory';
import userEvent from '@testing-library/user-event';
import { clearCategoryRepositoryCache } from '../../../../src/repositories/CategoryRepository';

const createSetCategoriesAction = (
  categoryCodes: CategoryCode[]
): SetCategoriesAction => {
  return {
    type: 'set',
    field: 'categories',
    value: categoryCodes,
  };
};

jest.mock('../../../../src/fetch/categoryTree.fetcher');
jest.mock('../../../../src/components/Select2Wrapper/Select2Wrapper');
jest.mock('../../../../src/dependenciesTools/provider/dependencies.ts');
jest.mock('../../../../src/dependenciesTools/AssetManager/AssetSelector');

describe('SetCategoriesActionLine', () => {
  beforeEach(() => {
    fetchMock.resetMocks();
    clearCategoryRepositoryCache();
  });

  it('should display the set categories action line, and switch tree', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get_categories?%7B%22identifiers%22:[%22cat1%22,%22cat2%22,%22cat3%22]%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify([
            createCategory('cat1', { root: 1 }),
            createCategory('cat2', { root: 200 }),
            createCategory('cat3', { root: 1 }),
          ])
        );
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });

    const defaultValues = {
      content: {
        actions: [
          {
            type: 'set',
            field: 'categories',
            value: ['cat1', 'cat2', 'cat3'],
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.actions[0].value', type: 'custom' },
      { name: 'content.actions[0].field', type: 'custom' },
      { name: 'content.actions[0].type', type: 'custom' },
    ];

    renderWithProviders(
      <SetCategoriesActionLine
        action={createSetCategoriesAction(['cat1', 'cat2', 'cat3'])}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      { all: true },
      { defaultValues, toRegister }
    );
    await waitForElementToBeRemoved(() =>
      document.querySelector('fieldset img[alt="pim_common.loading"]')
    ).then(() => {
      expect(
        screen.getByTestId('category-tree-selector-master')
      ).toBeInTheDocument();
      expect(
        screen.getByTestId('category-tree-selector-sales')
      ).toBeInTheDocument();
      expect(
        screen.queryByTestId('category-tree-selector-marketing')
      ).not.toBeInTheDocument(); // As there are no categories with this tree
      expect(
        screen.getByTestId('category-tree-selector-new')
      ).toBeInTheDocument();
      expect(
        screen.getByTestId('category-tree-selector-new').children.length
      ).toEqual(2); // Placeholder and 'marketing'
      expect(screen.getByTestId('category-selector-cat1')).toBeInTheDocument();
      expect(screen.getByTestId('category-selector-cat3')).toBeInTheDocument();
      expect(
        screen.queryByTestId('category-selector-cat2')
      ).not.toBeInTheDocument();
      expect(screen.getByTestId('category-selector-new')).toBeInTheDocument();
    });
    await act(async () => {
      userEvent.click(
        await screen.findByTestId('category-tree-selector-sales')
      );
    });
    expect(
      screen.queryByTestId('category-selector-cat1')
    ).not.toBeInTheDocument();
    expect(
      screen.queryByTestId('category-selector-cat3')
    ).not.toBeInTheDocument();
    expect(screen.getByTestId('category-selector-cat2')).toBeInTheDocument();
  });

  it('should display the unknown categories', async () => {
    fetchMock.mockResponse((request: Request) => {
      if (
        request.url.includes(
          'pimee_enrich_rule_definition_get_categories?%7B%22identifiers%22:[%22cat1%22,%22unexistingCategory%22,%22cat3%22]%7D'
        )
      ) {
        return Promise.resolve(
          JSON.stringify([
            createCategory('cat1', { root: 1 }),
            createCategory('cat3', { root: 1 }),
          ])
        );
      }
      throw new Error(`The "${request.url}" url is not mocked.`);
    });
    const defaultValues = {
      content: {
        actions: [
          {
            type: 'set',
            field: 'categories',
            value: ['cat1', 'unexistingCategory', 'cat3'],
          },
        ],
      },
    };

    const toRegister = [
      { name: 'content.actions[0].value', type: 'custom' },
      { name: 'content.actions[0].field', type: 'custom' },
      { name: 'content.actions[0].type', type: 'custom' },
    ];

    renderWithProviders(
      <SetCategoriesActionLine
        action={createSetCategoriesAction([
          'cat1',
          'unexistingCategory',
          'cat3',
        ])}
        lineNumber={0}
        locales={locales}
        scopes={scopes}
        currentCatalogLocale={'fr_FR'}
        handleDelete={jest.fn()}
      />,
      { all: true },
      { defaultValues, toRegister }
    );

    await waitForElementToBeRemoved(() =>
      document.querySelector('fieldset img[alt="pim_common.loading"]')
    ).then(() => {
      expect(
        screen.getByText('pimee_catalog_rule.exceptions.unknown_categories')
      ).toBeInTheDocument();
    });
  });
});
