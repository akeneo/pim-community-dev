import * as React from 'react';
import {Context} from 'akeneoassetmanager/domain/model/context';
import styled from 'styled-components';
import {FilterView, FilterViewCollection, getDataFilterViews} from 'akeneoassetmanager/application/configuration/value';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import {NormalizedOptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {NormalizedOptionCollectionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option-collection';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {AssetAttributeFetcher} from 'akeneoassetmanager/application/component/library/library';

export type FilterableAttribute = NormalizedOptionAttribute | NormalizedOptionCollectionAttribute;

export const sortFilterViewsByAttributeOrder = (filterViewCollection: FilterViewCollection) => {
  return [...filterViewCollection].sort(
    (filterViewA, filterviewB) => filterViewA.attribute.order - filterviewB.attribute.order
  );
};

export const useFilterViews = (
  assetFamilyIdentifier: AssetFamilyIdentifier | null,
  dataProvider: {assetAttributeFetcher: AssetAttributeFetcher}
): FilterViewCollection | null => {
  const [filterViews, setFilterViews] = React.useState<FilterViewCollection | null>(null);

  React.useEffect(() => {
    if (null === assetFamilyIdentifier) {
      return;
    }
    dataProvider.assetAttributeFetcher.fetchAll(assetFamilyIdentifier).then((attributes: NormalizedAttribute[]) => {
      setFilterViews(sortFilterViewsByAttributeOrder(getDataFilterViews(attributes)));
    });
  }, [assetFamilyIdentifier]);

  return filterViews;
};

const Filters = styled.div`
  padding-top: 30px;
  overflow-y: auto;
`;

const replaceFilter = (filterCollection: Filter[], filterToReplace: Filter) => {
  const notUpdatedFilters = filterCollection.filter(({field}: Filter) => field !== filterToReplace.field);

  return [...notUpdatedFilters, filterToReplace];
};

type FilterCollectionProps = {
  filterCollection: Filter[];
  context: Context;
  orderedFilterViews: FilterViewCollection;
  onFilterCollectionChange: (filterCollection: Filter[]) => void;
};

const FilterCollection = ({
  filterCollection,
  context,
  onFilterCollectionChange,
  orderedFilterViews,
}: FilterCollectionProps) => {
  return (
    <Filters>
      {orderedFilterViews.map((filterView: {view: FilterView; attribute: FilterableAttribute}) => {
        const View = filterView.view;
        const attribute = filterView.attribute;
        const filter = filterCollection.find((filter: Filter) => filter.field === getAttributeFilterKey(attribute));

        return (
          <div
            key={attribute.code}
            className="AknFilterBox-filterContainer AknFilterBox-filter AknFilterBox-filter--relative AknFilterBox-filter--smallMargin"
            data-attribute={attribute.code}
            data-type={attribute.type}
          >
            <View
              attribute={attribute}
              filter={filter}
              onFilterUpdated={(filter: Filter) => onFilterCollectionChange(replaceFilter(filterCollection, filter))}
              context={context}
            />
          </div>
        );
      })}
    </Filters>
  );
};

export default FilterCollection;
