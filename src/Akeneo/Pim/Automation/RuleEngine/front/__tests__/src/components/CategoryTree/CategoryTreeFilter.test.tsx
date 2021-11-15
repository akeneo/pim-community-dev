import React from 'react';
import userEvent from '@testing-library/user-event';
import {CategoryTreeFilter} from '../../../../src/components/CategoryTree/CategoryTreeFilter';
import {renderWithProviders, screen} from '../../../../test-utils';
import {Category} from '../../../../src/models';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from '../../../../src/components/CategoryTree/category-tree.types';
import {NetworkLifeCycle} from '../../../../src/components/CategoryTree/hooks/NetworkLifeCycle.types';

describe('CategoryTreeFilter', () => {
  it('should render the component with category tree master selected', async () => {
    // Given
    const categoryTrees: NetworkLifeCycle<CategoryTreeModel[]> = {
      status: 'COMPLETE',
      data: [
        {
          code: 'master',
          parent: null,
          labels: {
            en_US: 'Master catalog',
            de_DE: 'Hauptkatalog',
            fr_FR: 'Catalogue principal',
          },
          id: 1,
        },
        {
          code: 'sales',
          parent: null,
          labels: {
            en_US: 'Sales catalog',
            de_DE: 'Katalog Umsatz',
            fr_FR: 'Catalogue des ventes',
          },
          id: 2,
        },
      ],
    };
    const categoryTreesSelected: CategoryTreeModel = {
      code: 'master',
      parent: null,
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const initCategoryTreeOpenBranch: NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]> = {
      status: 'COMPLETE',
      data: [],
    };
    const onSelectCategory = jest.fn();
    const setCategoryTreeSelected = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTreeFilter
        categoryTrees={categoryTrees}
        categoryTreeSelected={categoryTreesSelected}
        initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
        locale={locale}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
        setCategoryTreeSelected={setCategoryTreeSelected}
      />,
      {
        all: true,
      }
    );
    // Then
    expect(await screen.findAllByText(/master catalog/i)).toHaveLength(3); // Selected tree / dropdown / tree
    expect(screen.getByText(/sales catalog/i)).toBeInTheDocument();
  });
  it('should render the component with a spinner when categories trees is still pending', () => {
    // Given
    const categoryTrees: NetworkLifeCycle<CategoryTreeModel[]> = {
      status: 'PENDING',
      data: [],
    };
    const categoryTreesSelected: CategoryTreeModel = {
      code: 'master',
      parent: null,
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const initCategoryTreeOpenBranch: NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]> = {
      status: 'COMPLETE',
      data: [],
    };
    const onSelectCategory = jest.fn();
    const setCategoryTreeSelected = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTreeFilter
        categoryTrees={categoryTrees}
        categoryTreeSelected={categoryTreesSelected}
        initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
        locale={locale}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
        setCategoryTreeSelected={setCategoryTreeSelected}
      />,
      {
        all: true,
      }
    );
    // Then
    expect(screen.getByTestId('akeneo-spinner')).toBeInTheDocument();
  });
  it('should render a spinner if initCategories is sill pending', () => {
    // Given
    const categoryTrees: NetworkLifeCycle<CategoryTreeModel[]> = {
      status: 'COMPLETE',
      data: [
        {
          code: 'master',
          parent: null,
          labels: {
            en_US: 'Master catalog',
            de_DE: 'Hauptkatalog',
            fr_FR: 'Catalogue principal',
          },
          id: 1,
        },
        {
          code: 'sales',
          parent: null,
          labels: {
            en_US: 'Sales catalog',
            de_DE: 'Katalog Umsatz',
            fr_FR: 'Catalogue des ventes',
          },
          id: 2,
        },
      ],
    };
    const categoryTreesSelected: CategoryTreeModel = {
      code: 'master',
      parent: null,
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const initCategoryTreeOpenBranch: NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]> = {
      status: 'PENDING',
      data: [],
    };
    const onSelectCategory = jest.fn();
    const setCategoryTreeSelected = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTreeFilter
        categoryTrees={categoryTrees}
        categoryTreeSelected={categoryTreesSelected}
        initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
        locale={locale}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
        setCategoryTreeSelected={setCategoryTreeSelected}
      />,
      {
        all: true,
      }
    );
    // Then
    expect(screen.getAllByText(/master catalog/i)).toHaveLength(2); // Selected tree / dropdown
    expect(screen.getByTestId('akeneo-spinner')).toBeInTheDocument();
    expect(screen.getByText(/sales catalog/i)).toBeInTheDocument();
  });
  it('should call setCategoryTreeSelected when clicking in the dropdown item', async () => {
    // Given
    const categoryTrees: NetworkLifeCycle<CategoryTreeModel[]> = {
      status: 'COMPLETE',
      data: [
        {
          code: 'master',
          parent: null,
          labels: {
            en_US: 'Master catalog',
            de_DE: 'Hauptkatalog',
            fr_FR: 'Catalogue principal',
          },
          id: 1,
        },
        {
          code: 'sales',
          parent: null,
          labels: {
            en_US: 'Sales catalog',
            de_DE: 'Katalog Umsatz',
            fr_FR: 'Catalogue des ventes',
          },
          id: 2,
        },
      ],
    };
    const categoryTreesSelected: CategoryTreeModel = {
      code: 'master',
      parent: null,
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const initCategoryTreeOpenBranch: NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]> = {
      status: 'COMPLETE',
      data: [],
    };
    const onSelectCategory = jest.fn();
    const setCategoryTreeSelected = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTreeFilter
        categoryTrees={categoryTrees}
        categoryTreeSelected={categoryTreesSelected}
        initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
        locale={locale}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
        setCategoryTreeSelected={setCategoryTreeSelected}
      />,
      {
        all: true,
      }
    );
    // Then
    expect(await screen.findAllByText(/master catalog/i)).toHaveLength(3); // Selected tree / dropdown / tree
    userEvent.click(screen.getByText(/sales catalog/i));
    expect(setCategoryTreeSelected).toHaveBeenCalledTimes(1);
    expect(setCategoryTreeSelected.mock.calls[0][0]).toEqual({
      code: 'sales',
      id: 2,
      labels: {
        de_DE: 'Katalog Umsatz',
        en_US: 'Sales catalog',
        fr_FR: 'Catalogue des ventes',
      },
      parent: null,
    });
  });
});
