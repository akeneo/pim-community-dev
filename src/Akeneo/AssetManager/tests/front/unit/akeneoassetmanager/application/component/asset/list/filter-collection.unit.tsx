import React from 'react';
import {fireEvent, screen} from '@testing-library/react';
import FilterCollection, {
  useFilterViews,
  sortFilterViewsByAttributeOrder,
} from 'akeneoassetmanager/application/component/asset/list/filter-collection';
import {denormalize} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {renderHook} from '@testing-library/react-hooks';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {AssetAttributeFetcher} from 'akeneoassetmanager/application/component/library/library';
import {FilterView} from 'akeneoassetmanager/application/configuration/value';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {FakeConfigProvider} from '../../../../utils/FakeConfigProvider';

const attributes = [
  {
    type: 'option',
    identifier: 'shooted_by_packshot_fingerprint',
    asset_family_identifier: 'packshot',
    code: 'shooted_by',
    order: 1,
    is_required: true,
    is_read_only: true,
    labels: {en_US: 'Shooted By'},
    value_per_locale: false,
    value_per_channel: false,
    options: [],
  },
  {
    type: 'option',
    identifier: 'created_by_packshot_fingerprint',
    asset_family_identifier: 'packshot',
    code: 'created_by',
    order: 0,
    is_required: true,
    is_read_only: true,
    labels: {en_US: 'Created by'},
    value_per_locale: false,
    value_per_channel: false,
    options: [],
  },
];
const dataProvider: {assetAttributeFetcher: AssetAttributeFetcher} = {
  assetAttributeFetcher: {
    fetchAll: () => {
      return new Promise(resolve => resolve(attributes));
    },
  },
};

describe('Tests filter collection', () => {
  it('It displays a filter collection in order of the attribute order', async () => {
    const FilterView: FilterView = ({attribute}) => <div data-code={attribute.code}></div>;

    const {container} = renderWithProviders(
      <FilterCollection
        orderedFilterViews={[
          {view: FilterView, attribute: denormalize(attributes[1])},
          {view: FilterView, attribute: denormalize(attributes[0])},
        ]}
        dataProvider={dataProvider}
        filterViewsProvider={{}}
        filterCollection={[]}
        assetFamilyIdentifier={'packshot'}
        context={{channel: 'ecommerce', locale: 'locale'}}
        onFilterCollectionChange={filterCollection => {}}
      />
    );

    const filters = [].slice.call(container.querySelectorAll('[data-code]'));
    expect(filters.map(({dataset}) => dataset.code)).toEqual(['created_by', 'shooted_by']);
  });

  it('It displays an empty filter collection', () => {
    const {container} = renderWithProviders(
      <FilterCollection
        orderedFilterViews={[]}
        dataProvider={dataProvider}
        filterViewsProvider={{getFilterViews: () => []}}
        filterCollection={[]}
        assetFamilyIdentifier={'packshot'}
        context={{channel: 'ecommerce', locale: 'locale'}}
        onFilterCollectionChange={jest.fn()}
      />
    );

    expect(container.querySelectorAll('[data-attribute]')).toHaveLength(0);
  });

  it('It updates the filter collection', () => {
    const filterContent = 'MY_FILTER';
    const expectedFilterCollection = [{field: attributes[0].code, operator: '=', value: 'nice'}];
    const ClickableFilterView = ({onFilterUpdated}) => (
      <div
        data-testid="my-filter"
        onClick={() => {
          onFilterUpdated(expectedFilterCollection[0]);
        }}
      >
        {filterContent}
      </div>
    );

    const handleFilterCollectionChange = jest.fn();
    const actualFilterCollection: Filter[] = [{field: attributes[0].code, operator: 'IN', value: [], context: {}}];
    renderWithProviders(
      <FilterCollection
        orderedFilterViews={[{view: ClickableFilterView, attribute: denormalize(attributes[0])}]}
        dataProvider={dataProvider}
        filterViewsProvider={{}}
        filterCollection={actualFilterCollection}
        assetFamilyIdentifier={'packshot'}
        context={{channel: 'ecommerce', locale: 'locale'}}
        onFilterCollectionChange={handleFilterCollectionChange}
      />
    );

    fireEvent.click(screen.getByTestId('my-filter'));

    expect(handleFilterCollectionChange).toBeCalledWith(expectedFilterCollection);
  });

  test('I get a null collection view if there is no attributes', async () => {
    const {result, waitForNextUpdate} = renderHook(
      () =>
        useFilterViews('notice', {
          assetAttributeFetcher: {
            fetchAll: () => new Promise(resolve => resolve([])),
          },
        }),
      {wrapper: FakeConfigProvider}
    );

    expect(result.current).toBe(null);
    await waitForNextUpdate();

    expect(result.current).toEqual([]);
  });

  test('I get an empty collection view if there is no attributes', async () => {
    const {result, waitForNextUpdate} = renderHook(() => useFilterViews('notice', dataProvider), {
      wrapper: FakeConfigProvider,
    });

    expect(result.current).toBe(null);
    await waitForNextUpdate();

    expect(result.current).toEqual([]);
  });

  test('I get an empty collection view if the asset family is null', () => {
    const {result} = renderHook(() => useFilterViews(null, dataProvider), {wrapper: FakeConfigProvider});

    expect(result.current).toBe(null);
  });

  test('I can sort attributes in the collection', () => {
    const sortedAttributes = sortFilterViewsByAttributeOrder(attributes.map(attribute => ({attribute, view: null})));

    expect(sortedAttributes).toEqual([
      {
        attribute: {
          type: 'option',
          identifier: 'created_by_packshot_fingerprint',
          asset_family_identifier: 'packshot',
          code: 'created_by',
          order: 0,
          is_required: true,
          is_read_only: true,
          labels: {en_US: 'Created by'},
          value_per_locale: false,
          value_per_channel: false,
          options: [],
        },
        view: null,
      },
      {
        attribute: {
          type: 'option',
          identifier: 'shooted_by_packshot_fingerprint',
          asset_family_identifier: 'packshot',
          code: 'shooted_by',
          order: 1,
          is_required: true,
          is_read_only: true,
          labels: {en_US: 'Shooted By'},
          value_per_locale: false,
          value_per_channel: false,
          options: [],
        },
        view: null,
      },
    ]);
  });
});
