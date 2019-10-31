import * as React from 'react';
import {Context} from 'akeneopimenrichmentassetmanager/platform/model/context';
import styled from 'styled-components';
import {FilterView} from 'akeneoassetmanager/application/configuration/value';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {getAttributeFilterKey} from 'akeneoassetmanager/tools/filter';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';
import {
  FilterViewCollection,
  FilterableAttribute,
} from 'akeneopimenrichmentassetmanager/assets-collection/infrastructure/component/asset-picker';

const Container = styled.div`
  display: flex;
  flex-shrink: 0;
  flex-direction: column;
  width: 300px;
  padding-right: 20px;
  padding-left: 30px;
  border-right: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80}
  overflow-y: auto;
`;

const Title = styled.div`
  padding-bottom: 10px;
  padding-top: 4px;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey100};
  text-transform: uppercase;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  background-color: white;
`;

const Filters = styled.div`
  padding-top: 16px;
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
    <React.Fragment>
      {orderedFilterViews.length !== 0 ? (
        <Container data-container="filter-collection">
          <Title>{__('pim_asset_manager.asset_picker.filter.title')}</Title>
          <Filters>
            {orderedFilterViews.map((filterView: {view: FilterView; attribute: FilterableAttribute}) => {
              const View = filterView.view;
              const attribute = filterView.attribute;
              const filter = filterCollection.find(
                (filter: Filter) => filter.field === getAttributeFilterKey(attribute)
              );

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
          </Filters>
        </Container>
      ) : null}
    </React.Fragment>
  );
};

export default FilterCollection;
