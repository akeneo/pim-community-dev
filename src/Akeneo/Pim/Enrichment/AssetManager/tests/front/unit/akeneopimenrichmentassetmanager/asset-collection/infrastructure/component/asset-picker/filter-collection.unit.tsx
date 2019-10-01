import * as React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {render, act, fireEvent} from '@testing-library/react';
import {ThemeProvider} from 'styled-components';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import FilterCollection from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker/filter-collection';
import {denormalize} from 'akeneoassetmanager/domain/model/attribute/type/option';
import * as ReactDOM from 'react-dom';

const attributes = [
  {
    type: 'option',
    identifier: 'shooted_by_packshot_fingerprint',
    asset_family_identifier: 'packshot',
    code: 'shooted_by',
    order: 1,
    is_required: true,
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
    labels: {en_US: 'Created by'},
    value_per_locale: false,
    value_per_channel: false,
    options: [],
  },
];
const dataProvider = {
  assetAttributesFetcher: {
    fetchAll: assetFamilyIdentifier => {
      return new Promise(resolve => {
        act(() => {
          resolve(attributes);
        });
      });
    },
  },
};

let container;
describe('Tests filter collection', () => {
  beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
  });

  afterEach(() => {
    document.body.removeChild(container);
    container = null;
  });

  it('It displays a filter collection in order of the attribute order', async () => {
    const FilterView = ({attribute}) => <div data-code={attribute.code}></div>;

    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <FilterCollection
            dataProvider={dataProvider}
            filterViewsProvider={{
              getFilterViews: () => [
                {view: FilterView, attribute: denormalize(attributes[0])},
                {view: FilterView, attribute: denormalize(attributes[1])},
              ],
            }}
            filterCollection={[]}
            assetFamilyIdentifier={'packshot'}
            context={{channel: 'ecommerce', locale: 'locale'}}
            onFilterCollectionChange={filterCollection => {}}
          />
        </ThemeProvider>,
        container
      );
    });
    const filters = [].slice.call(container.querySelectorAll('[data-code]'));
    expect(filters.map(({dataset}) => dataset.code)).toEqual(['created_by', 'shooted_by']);
  });

  it('It displays an empty filter collection', async () => {
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <FilterCollection
            dataProvider={dataProvider}
            filterViewsProvider={{getFilterViews: () => []}}
            filterCollection={[]}
            assetFamilyIdentifier={'packshot'}
            context={{channel: 'ecommerce', locale: 'locale'}}
            onFilterCollectionChange={filterCollection => {}}
          />
        </ThemeProvider>,
        container
      );
    });

    expect(container.childNodes.length).toEqual(0);
  });

  it('It updates the filter collection', async () => {
    const filterContent = 'MY_FILTER';
    const expectedFilterCollection = [{field: attributes[0].code, operator: '=', value: 'nice'}];
    const ClickableFilterView = ({onFilterUpdated}) => (
      <div
        className="my-filter"
        onClick={() => {
          onFilterUpdated(expectedFilterCollection[0]);
        }}
      >
        {filterContent}
      </div>
    );

    let actualFilterCollection = [{field: attributes[0].code, operator: 'IN', value: []}];
    await act(async () => {
      ReactDOM.render(
        <ThemeProvider theme={akeneoTheme}>
          <FilterCollection
            dataProvider={dataProvider}
            filterViewsProvider={{
              getFilterViews: () => [{view: ClickableFilterView, attribute: denormalize(attributes[0])}],
            }}
            filterCollection={actualFilterCollection}
            assetFamilyIdentifier={'packshot'}
            context={{channel: 'ecommerce', locale: 'locale'}}
            onFilterCollectionChange={filterCollection => {
              actualFilterCollection = filterCollection;
            }}
          />
        </ThemeProvider>,
        container
      );
    });

    fireEvent.click(container.querySelector('.my-filter'));

    expect(actualFilterCollection).toEqual(expectedFilterCollection);
  });
});
