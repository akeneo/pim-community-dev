import * as React from 'react';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import styled from 'styled-components';
import {FilterView} from 'akeneoassetmanager/application/configuration/value';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {FilterViewCollection} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';
import {OptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {OptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {AssetAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset';
import {AssetCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/asset-collection';

type FilterCollectionProps = {
  dataProvider: any;
  filterViewsProvider: {
    getFilterViews: (attributes: NormalizedAttribute[]) => FilterViewCollection;
  };
  filterCollection: Filter[];
  assetFamilyIdentifier: AssetFamilyIdentifier;
  context: Context;
  onFilterCollectionChange: (filterCollection: any[]) => void;
};

type FilterableAttribute = OptionAttribute | OptionCollectionAttribute | AssetAttribute | AssetCollectionAttribute;

const Container = styled.div`
  display: flex;
  flex-shrink: 0;
  flex-direction: column;
  width: 280px;
  padding-right: 20px;
  border-right: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80}
  overflow: auto;
`;

const Title = styled.span`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  margin-bottom: 16px;
  position: sticky;
  top: 0;
  background-color: white;
  z-index: 1000;
  padding-bottom: 10px;
  padding-top: 4px;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
`;

const replaceFilter = (filterCollection: Filter[], filterToReplace: Filter) => {
  const notUpdatedFilters = filterCollection.filter(({field}: Filter) => field !== filterToReplace.field);

  return [...notUpdatedFilters, filterToReplace];
};

const FilterCollection = ({
  filterCollection,
  context,
  assetFamilyIdentifier,
  onFilterCollectionChange,
  dataProvider,
  filterViewsProvider,
}: FilterCollectionProps) => {
  const filterViews = useFilterViews(assetFamilyIdentifier, dataProvider, filterViewsProvider);
  const orderedFilterViews = [...filterViews].sort(
    (
      filterViewA: {
        view: FilterView;
        attribute: FilterableAttribute;
      },
      filterviewB: {
        view: FilterView;
        attribute: FilterableAttribute;
      }
    ) => filterViewA.attribute.order - filterviewB.attribute.order
  );
  return (
    <React.Fragment>
      {orderedFilterViews.length !== 0 ? (
        <Container>
          <Title>{__('pim_asset_manager.asset_picker.filter.title')}</Title>
          {orderedFilterViews.map((filterView: {view: FilterView; attribute: FilterableAttribute}) => {
            const View = filterView.view;
            const attribute = filterView.attribute;
            const filter = filterCollection.find((filter: Filter) => filter.field === getAttributeFilterKey(attribute));

            return (
              <div
                key={attribute.getCode()}
                className="AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin"
                data-attribute={attribute.getCode()}
                data-type={attribute.getType()}
              >
                <View
                  attribute={attribute}
                  filter={filter}
                  onFilterUpdated={(filter: Filter) =>
                    onFilterCollectionChange(replaceFilter(filterCollection, filter))
                  }
                  context={context}
                />
              </div>
            );
          })}
        </Container>
      ) : null}
    </React.Fragment>
  );
};

const useFilterViews = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  dataProvider: any,
  filterViewsProvider: {
    getFilterViews: (attributes: NormalizedAttribute[]) => FilterViewCollection;
  }
) => {
  const [filterViews, setFilterViews] = React.useState<FilterViewCollection>([]);

  React.useEffect(() => {
    dataProvider.assetAttributesFetcher.fetchAll(assetFamilyIdentifier).then((attributes: NormalizedAttribute[]) => {
      setFilterViews(filterViewsProvider.getFilterViews(attributes));
    });
  }, [assetFamilyIdentifier]);

  return filterViews;
};

export default FilterCollection;
