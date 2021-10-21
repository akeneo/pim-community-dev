import React, {useEffect, useRef} from 'react';
import styled from 'styled-components';
import {Helper, SectionTitle, useTabBar} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {
  addAssociationTypeSource,
  addAttributeSource,
  addPropertySource,
  ColumnConfiguration,
  removeSource,
  updateSource,
  Source,
  MAX_SOURCE_COUNT,
  Format,
} from '../../models';
import {AddSourceDropdown} from './AddSourceDropdown/AddSourceDropdown';
import {AttributeSourceConfigurator} from '../SourceDetails/AttributeSourceConfigurator';
import {PropertySourceConfigurator} from '../SourceDetails/PropertySourceConfigurator';
import {AssociationTypeSourceConfigurator} from '../SourceDetails/AssociationTypeSourceConfigurator';
import {SourceTabBar} from '../SourceDetails/SourceTabBar';
import {useFetchers, useValidationErrors} from '../../contexts';
import {useChannels} from '../../hooks';
import {NoSourcePlaceholder} from './ColumnDetailsPlaceholder';
import {SourceFooter} from './SourceFooter';
import {SourcesConcatenation} from './SourcesConcatenation/SourcesConcatenation';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 400px;
  display: flex;
  flex-direction: column;
`;

const ConfiguratorContainer = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
`;

const SourcesContent = styled.div`
  display: flex;
  flex-direction: column;
  flex: 1;
`;

type ColumnDetailsProps = {
  columnConfiguration: ColumnConfiguration;
  onColumnChange: (column: ColumnConfiguration) => void;
};

const ColumnDetails = ({columnConfiguration, onColumnChange}: ColumnDetailsProps) => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const translate = useTranslate();
  const channels = useChannels();
  const firstSource = columnConfiguration.sources[0]?.uuid ?? null;
  const [isCurrent, switchTo, currentSourceUuid] = useTabBar(firstSource);

  const currentSource = columnConfiguration.sources.find(({uuid}) => isCurrent(uuid)) ?? null;

  const handleSourceChange = (updatedSource: Source) => {
    onColumnChange(updateSource(columnConfiguration, updatedSource));
  };

  const handleSourceRemove = (currentSource: Source) => {
    onColumnChange(removeSource(columnConfiguration, currentSource));
    switchTo(firstSource);
  };

  const handleFormatChange = (format: Format) => onColumnChange({...columnConfiguration, format});

  const attributeFetcher = useFetchers().attribute;
  const associationTypeFetcher = useFetchers().associationType;

  const handleSourceAdd = async (addedSourceCode: string, sourceType: string) => {
    if (sourceType === 'property') {
      const updatedColumnConfiguration = addPropertySource(columnConfiguration, addedSourceCode, channels);
      switchTo(updatedColumnConfiguration.sources[updatedColumnConfiguration.sources.length - 1]?.uuid ?? '');
      onColumnChange(updatedColumnConfiguration);
    } else if (sourceType === 'association_type') {
      const [associationType] = await associationTypeFetcher.fetchByCodes([addedSourceCode]);
      const updatedColumnConfiguration = addAssociationTypeSource(columnConfiguration, associationType);
      switchTo(updatedColumnConfiguration.sources[updatedColumnConfiguration.sources.length - 1]?.uuid ?? '');
      onColumnChange(updatedColumnConfiguration);
    } else {
      const [attribute] = await attributeFetcher.fetchByIdentifiers([addedSourceCode]);
      const updatedColumnConfiguration = addAttributeSource(columnConfiguration, attribute, channels);
      switchTo(updatedColumnConfiguration.sources[updatedColumnConfiguration.sources.length - 1]?.uuid ?? '');
      onColumnChange(updatedColumnConfiguration);
    }
  };

  const sourcesErrors = useValidationErrors(`[columns][${columnConfiguration.uuid}][sources]`, true);
  const validationErrors = useValidationErrors(`[columns][${columnConfiguration.uuid}][sources]`, false);
  const formatErrors = useValidationErrors(`[columns][${columnConfiguration.uuid}][format]`, false);

  useEffect(() => {
    switchTo(firstSource);
  }, [switchTo, firstSource]);

  return (
    <Container ref={scrollRef}>
      <SourcesContent>
        <SectionTitle sticky={0}>
          <SectionTitle.Title>{translate('akeneo.tailored_export.column_details.sources.title')}</SectionTitle.Title>
          <SectionTitle.Spacer />
          <AddSourceDropdown
            canAddSource={columnConfiguration.sources.length < MAX_SOURCE_COUNT}
            onSourceSelected={handleSourceAdd}
          />
        </SectionTitle>
        {columnConfiguration.sources.length !== 0 && (
          <SourceTabBar
            validationErrors={validationErrors}
            sources={columnConfiguration.sources}
            currentTab={currentSourceUuid}
            onTabChange={switchTo}
          />
        )}
        {sourcesErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        <ConfiguratorContainer>
          {'attribute' === currentSource?.type && (
            <AttributeSourceConfigurator
              source={currentSource}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {'property' === currentSource?.type && (
            <PropertySourceConfigurator
              source={currentSource}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {'association_type' === currentSource?.type && (
            <AssociationTypeSourceConfigurator
              source={currentSource}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {0 === columnConfiguration.sources.length && <NoSourcePlaceholder />}
        </ConfiguratorContainer>
        {currentSource && <SourceFooter source={currentSource} onSourceRemove={handleSourceRemove} />}
      </SourcesContent>
      {0 < columnConfiguration.sources.length && (
        <SourcesConcatenation
          validationErrors={formatErrors}
          sources={columnConfiguration.sources}
          format={columnConfiguration.format}
          onFormatChange={handleFormatChange}
          scrollRef={scrollRef}
        />
      )}
    </Container>
  );
};

export {ColumnDetails};
