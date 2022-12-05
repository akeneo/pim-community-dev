import {useStorageState, useTranslate} from '@akeneo-pim-community/shared';
import {TabBar} from 'akeneo-design-system';
import React, {useCallback} from 'react';
import styled from 'styled-components';
import {DataMappingConfigurator} from './DataMappingConfigurator';
import {FilterConfigurator} from './FilterConfigurator';
import {CatalogProjection, RequirementCollection, DataMapping} from './models';
import {ProductSelectionValues} from '@akeneo-pim-community/catalogs';

const Container = styled.div`
  & > header {
    // crazy dirty, should be fixed
    padding: 0;
    margin-bottom: 10px;
  }
`;

type CatalogProjectionConfiguratorProps = {
  requirements: RequirementCollection;
  catalogProjection: CatalogProjection;
  onSave: () => void;
  onCatalogProjectionChange: (catalogProjection: CatalogProjection) => void;
};

const CatalogProjectionConfigurator = ({
  requirements,
  catalogProjection,
  onCatalogProjectionChange,
}: CatalogProjectionConfiguratorProps) => {
  const translate = useTranslate();
  const [currentTab, setCurrentTab] = useStorageState<'filter' | 'data_mapping'>(
    'data_mapping',
    'syndication-catalog-projection-configurator-tab'
  );

  const handleDataMappingsConfigurationChange = useCallback(
    (dataMappings: DataMapping[]) => {
      onCatalogProjectionChange({...catalogProjection, dataMappings});
    },
    [catalogProjection, onCatalogProjectionChange]
  );

  const handleFiltersConfigurationChange = useCallback(
    (filters: ProductSelectionValues) => {
      onCatalogProjectionChange({...catalogProjection, filters});
    },
    [catalogProjection, onCatalogProjectionChange]
  );

  return (
    <Container>
      <TabBar moreButtonTitle={translate('pim_common.more')}>
        <TabBar.Tab isActive={'data_mapping' === currentTab} onClick={() => setCurrentTab('data_mapping')}>
          {translate('akeneo.syndication.configuration.data_mapping')}
        </TabBar.Tab>
        <TabBar.Tab isActive={'filter' === currentTab} onClick={() => setCurrentTab('filter')}>
          {translate('akeneo.syndication.configuration.filter')}
        </TabBar.Tab>
      </TabBar>
      {'data_mapping' === currentTab && (
        <DataMappingConfigurator
          requirements={requirements}
          dataMappings={catalogProjection.dataMappings}
          onDataMappingsConfigurationChange={handleDataMappingsConfigurationChange}
        />
      )}
      {'filter' === currentTab && (
        <FilterConfigurator
          filters={catalogProjection.filters}
          onFiltersConfigurationChange={handleFiltersConfigurationChange}
        />
      )}
    </Container>
  );
};

export {CatalogProjectionConfigurator};
