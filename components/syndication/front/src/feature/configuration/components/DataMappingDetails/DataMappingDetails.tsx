import React, {useEffect, useRef} from 'react';
import styled from 'styled-components';
import {Helper, SectionTitle, useTabBar} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {
  addAssociationTypeSource,
  addAttributeSource,
  addPropertySource,
  addStaticSource,
  DataMapping,
  removeSource,
  updateSource,
  Source,
  MAX_SOURCE_COUNT,
  Format,
  getRequirementLabel,
} from '../../models';
import {AddSourceDropdown} from './AddSourceDropdown/AddSourceDropdown';
import {AttributeSourceConfigurator} from '../SourceDetails/AttributeSourceConfigurator';
import {PropertySourceConfigurator} from '../SourceDetails/PropertySourceConfigurator';
import {AssociationTypeSourceConfigurator} from '../SourceDetails/AssociationTypeSourceConfigurator';
import {SourceTabBar} from '../SourceDetails/SourceTabBar';
import {useFetchers, useValidationErrors} from '../../contexts';
import {useChannels} from '../../hooks';
import {NoSourcePlaceholder} from './DataMappingDetailsPlaceholder';
import {SourceFooter} from './SourceFooter';
import {SourcesConcatenation} from './SourcesConcatenation/SourcesConcatenation';
import {useRequirement} from '../../contexts/RequirementsContext';
import {supportMultipleSources, ConcatFormat} from '../../models';
import {StaticSourceConfigurator} from '../SourceDetails/StaticSourceConfigurator';
import {PossibleValues} from './PossibleValues';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 600px;
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

type DataMappingDetailsProps = {
  dataMapping: DataMapping;
  onDataMappingChange: (dataMapping: DataMapping) => void;
};

const DataMappingDetails = ({dataMapping, onDataMappingChange}: DataMappingDetailsProps) => {
  const scrollRef = useRef<HTMLDivElement>(null);
  const translate = useTranslate();
  const channels = useChannels();
  const firstSource = dataMapping.sources[0]?.uuid ?? null;
  const [isCurrent, switchTo, currentSourceUuid] = useTabBar(firstSource);

  const currentSource = dataMapping.sources.find(({uuid}) => isCurrent(uuid)) ?? null;

  const handleSourceChange = (updatedSource: Source) => {
    onDataMappingChange(updateSource(dataMapping, updatedSource));
  };

  const handleSourceRemove = (currentSource: Source) => {
    onDataMappingChange(removeSource(dataMapping, currentSource));
    switchTo(firstSource);
  };

  const handleFormatChange = (format: Format) => onDataMappingChange({...dataMapping, format});

  const attributeFetcher = useFetchers().attribute;
  const associationTypeFetcher = useFetchers().associationType;

  const handleSourceAdd = async (addedSourceCode: string, sourceType: string) => {
    if (sourceType === 'property') {
      const updatedDataMapping = addPropertySource(dataMapping, addedSourceCode, channels);
      switchTo(updatedDataMapping.sources[updatedDataMapping.sources.length - 1]?.uuid ?? '');
      onDataMappingChange(updatedDataMapping);
    } else if (sourceType === 'static') {
      const updatedDataMapping = addStaticSource(dataMapping, addedSourceCode, channels);
      switchTo(updatedDataMapping.sources[updatedDataMapping.sources.length - 1]?.uuid ?? '');
      onDataMappingChange(updatedDataMapping);
    } else if (sourceType === 'association_type') {
      const [associationType] = await associationTypeFetcher.fetchByCodes([addedSourceCode]);
      const updatedDataMapping = addAssociationTypeSource(dataMapping, associationType);
      switchTo(updatedDataMapping.sources[updatedDataMapping.sources.length - 1]?.uuid ?? '');
      onDataMappingChange(updatedDataMapping);
    } else {
      const [attribute] = await attributeFetcher.fetchByIdentifiers([addedSourceCode]);
      const updatedDataMapping = addAttributeSource(dataMapping, attribute, channels);
      switchTo(updatedDataMapping.sources[updatedDataMapping.sources.length - 1]?.uuid ?? '');
      onDataMappingChange(updatedDataMapping);
    }
  };

  const sourcesErrors = useValidationErrors(`[data_mappings][${dataMapping.uuid}][sources]`, true);
  const validationErrors = useValidationErrors(`[data_mappings][${dataMapping.uuid}][sources]`, false);
  const formatErrors = useValidationErrors(`[data_mappings][${dataMapping.uuid}][format]`, false);
  const requirement = useRequirement(dataMapping.target.name);

  useEffect(() => {
    switchTo(firstSource);
  }, [switchTo, firstSource]);

  if (null === requirement) {
    return null;
  }

  return (
    <Container ref={scrollRef}>
      <SourcesContent>
        <SectionTitle sticky={0}>
          <SectionTitle.Title>{getRequirementLabel(requirement)}</SectionTitle.Title>
          <SectionTitle.Spacer />
          <AddSourceDropdown
            canAddSource={
              dataMapping.sources.length < MAX_SOURCE_COUNT &&
              (supportMultipleSources(requirement) || dataMapping.sources.length === 0)
            }
            type={requirement.type}
            onSourceSelected={handleSourceAdd}
          />
        </SectionTitle>
        <Helper level="info">{requirement.help}</Helper>
        {requirement.options?.suggestedValues && (
          <PossibleValues label={'Suggested values'} values={requirement.options?.suggestedValues} />
        )}
        {requirement.options?.possibleValues && (
          <PossibleValues label={'Possible values'} values={requirement.options?.possibleValues} />
        )}
        {dataMapping.sources.length !== 0 && (
          <SourceTabBar
            validationErrors={validationErrors}
            sources={dataMapping.sources}
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
              requirement={requirement}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {'property' === currentSource?.type && (
            <PropertySourceConfigurator
              source={currentSource}
              requirement={requirement}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {'static' === currentSource?.type && (
            <StaticSourceConfigurator
              source={currentSource}
              requirement={requirement}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {'association_type' === currentSource?.type && (
            <AssociationTypeSourceConfigurator
              source={currentSource}
              requirement={requirement}
              validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
              onSourceChange={handleSourceChange}
            />
          )}
          {0 === dataMapping.sources.length && <NoSourcePlaceholder />}
        </ConfiguratorContainer>
        {currentSource && <SourceFooter source={currentSource} onSourceRemove={handleSourceRemove} />}
      </SourcesContent>
      {0 < dataMapping.sources.length && (
        <>
          {'string' === requirement.type && (
            <SourcesConcatenation
              validationErrors={formatErrors}
              sources={dataMapping.sources}
              format={dataMapping.format as ConcatFormat}
              onFormatChange={handleFormatChange}
              scrollRef={scrollRef}
            />
          )}
        </>
      )}
    </Container>
  );
};

export {DataMappingDetails};
