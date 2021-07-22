import React from 'react';
import userEvent from '@testing-library/user-event';
import {CategoryTree} from '../../../../../src/components/CategoryTree/components/CategoryTree';
import {renderWithProviders, screen} from '../../../../../test-utils';
import {Category} from '../../../../../src/models';
import {CategoryTreeModel} from '../../../../../src/components/CategoryTree/category-tree.types';
import {fetchCategoryTreeChildren} from '../../../../../src/fetch/categoryTree.fetcher';

describe('SelectCategoriesTrees', () => {
  beforeEach(() => jest.clearAllMocks());
  const mockFetchCategoryTreeChildren = fetchCategoryTreeChildren as jest.Mock<
    any
  >;
  it('should render the master tree category opened with some children with one opened in locale en_US', async () => {
    // Given
    const masterChildrenMock = {
      attr: {
        id: 'node_1',
        'data-code': 'master',
      },
      data: 'Master catalog',
      state: 'closed jstree-root',
      children: [
        {
          attr: {
            id: 'node_3',
            'data-code': 'tvs_projectors',
          },
          data: 'TVs and projectors',
          state: 'closed',
        },
        {
          attr: {
            id: 'node_6',
            'data-code': 'cameras',
          },
          data: 'Cameras',
          state: 'closed',
        },
        {
          attr: {
            id: 'node_10',
            'data-code': 'audio_video',
          },
          data: 'Audio and Video',
          state: 'open',
        },
      ],
    };
    const audioVideoChildrenMock = {
      attr: {
        id: 'node_10',
        'data-code': 'audio_video',
      },
      data: 'Audio and Video',
      state: 'closed',
      children: [
        {
          attr: {
            id: 'node_11',
            'data-code': 'headphones',
          },
          data: 'Headphones',
          state: 'leaf',
        },
        {
          attr: {
            id: 'node_12',
            'data-code': 'mp3_players',
          },
          data: 'MP3 players',
          state: 'leaf',
        },
      ],
    };
    mockFetchCategoryTreeChildren
      .mockImplementationOnce(() =>
        Promise.resolve({
          ok: true,
          json: () => masterChildrenMock,
        })
      )
      .mockImplementationOnce(() =>
        Promise.resolve({
          ok: true,
          json: () => audioVideoChildrenMock,
        })
      );
    const categoryTree: CategoryTreeModel = {
      code: 'master',
      parent: '',
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const onSelectCategory = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTree
        locale={locale}
        categoryTree={categoryTree}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
      />,
      {all: true}
    );
    // Then
    expect(screen.getByText(categoryTree.labels[locale])).toBeInTheDocument();
    expect(await screen.findByText(/tvs and projectors/i)).toBeInTheDocument();
    expect(screen.getByText(/cameras/i)).toBeInTheDocument();
    expect(screen.getByText(/audio and video/i)).toBeInTheDocument();
    expect(screen.getByText(/headphones/i)).toBeInTheDocument();
    expect(screen.getByText(/MP3 players/i)).toBeInTheDocument();
  });
  it('should opened the categories Audio and Video on click on the arrow button associated ', async () => {
    // Given
    const masterChildrenMock = {
      attr: {
        id: 'node_1',
        'data-code': 'master',
      },
      data: 'Master catalog',
      state: 'closed jstree-root',
      children: [
        {
          attr: {
            id: 'node_10',
            'data-code': 'audio_video',
          },
          data: 'Audio and Video',
          state: 'closed',
        },
      ],
    };
    const audioVideoChildrenMock = {
      attr: {
        id: 'node_10',
        'data-code': 'audio_video',
      },
      data: 'Audio and Video',
      state: 'closed',
      children: [
        {
          attr: {
            id: 'node_11',
            'data-code': 'headphones',
          },
          data: 'Headphones',
          state: 'leaf',
        },
        {
          attr: {
            id: 'node_12',
            'data-code': 'mp3_players',
          },
          data: 'MP3 players',
          state: 'leaf',
        },
      ],
    };
    mockFetchCategoryTreeChildren
      .mockImplementationOnce(() =>
        Promise.resolve({
          ok: true,
          json: () => masterChildrenMock,
        })
      )
      .mockImplementationOnce(() =>
        Promise.resolve({
          ok: true,
          json: () => audioVideoChildrenMock,
        })
      );
    const categoryTree: CategoryTreeModel = {
      code: 'master',
      parent: '',
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const onSelectCategory = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTree
        locale={locale}
        categoryTree={categoryTree}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
      />,
      {all: true}
    );
    expect(screen.getByText(categoryTree.labels[locale])).toBeInTheDocument();
    expect(await screen.findByText(/master catalog/i)).toBeInTheDocument();
    userEvent.click(screen.getByTestId('tree-arrow-button-audio_video'));
    // Then
    expect(await screen.findByText(/headphones/i)).toBeInTheDocument();
    expect(screen.getByText(/MP3 players/i)).toBeInTheDocument();
  });
  it('should call on select when clicking on the tree node label', async () => {
    // Given
    const masterChildrenMock = {
      attr: {
        id: 'node_1',
        'data-code': 'master',
      },
      data: 'Master catalog',
      state: 'closed jstree-root',
      children: [
        {
          attr: {
            id: 'node_10',
            'data-code': 'audio_video',
          },
          data: 'Audio and Video',
          state: 'closed',
        },
      ],
    };
    mockFetchCategoryTreeChildren.mockImplementationOnce(() =>
      Promise.resolve({
        ok: true,
        json: () => masterChildrenMock,
      })
    );
    const categoryTree: CategoryTreeModel = {
      code: 'master',
      parent: '',
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const onSelectCategory = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTree
        locale={locale}
        categoryTree={categoryTree}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
      />,
      {all: true}
    );
    expect(screen.getByText(categoryTree.labels[locale])).toBeInTheDocument();
    expect(await screen.findByText(/master catalog/i)).toBeInTheDocument();
    userEvent.click(screen.getByText(/audio and video/i));
    // Then
    expect(onSelectCategory).toHaveBeenNthCalledWith(1, 'audio_video');
  });
  it('should not call on select when clicking on the tree root node label', async () => {
    // Given
    const masterChildrenMock = {
      attr: {
        id: 'node_1',
        'data-code': 'master',
      },
      data: 'Master catalog',
      state: 'closed jstree-root',
      children: [
        {
          attr: {
            id: 'node_10',
            'data-code': 'audio_video',
          },
          data: 'Audio and Video',
          state: 'closed',
        },
      ],
    };
    mockFetchCategoryTreeChildren.mockImplementationOnce(() =>
      Promise.resolve({
        ok: true,
        json: () => masterChildrenMock,
      })
    );
    const categoryTree: CategoryTreeModel = {
      code: 'master',
      parent: '',
      labels: {
        en_US: 'Master catalog',
        de_DE: 'Hauptkatalog',
        fr_FR: 'Catalogue principal',
      },
      id: 1,
    };
    const onSelectCategory = jest.fn();
    const selectedCategories: Category[] = [];
    const locale = 'en_US';
    // When
    renderWithProviders(
      <CategoryTree
        locale={locale}
        categoryTree={categoryTree}
        onSelectCategory={onSelectCategory}
        selectedCategories={selectedCategories}
      />,
      {all: true}
    );
    expect(screen.getByText(categoryTree.labels[locale])).toBeInTheDocument();
    expect(await screen.findByText(/master catalog/i)).toBeInTheDocument();
    userEvent.click(screen.getByText(/master catalog/i));
    // Then
    expect(onSelectCategory).not.toHaveBeenCalledWith('master');
  });
});
