import React, {useEffect} from 'react';
import styled from 'styled-components';
import {Helper, SectionTitle, useTabBar} from 'akeneo-design-system';
import {filterErrors, useTranslate} from '@akeneo-pim-community/shared';
import {
  addAttributeSource,
  addPropertySource,
  ColumnConfiguration,
  removeSource,
  Source,
  updateSource,
} from '../../models/ColumnConfiguration';
import {AddSourceDropdown} from './AddSourceDropdown/AddSourceDropdown';
import {SourceConfigurator} from '../SourceDetails/SourceConfigurator';
import {SourceTabBar} from '../SourceDetails/SourceTabBar';
import {useFetchers, useValidationErrors} from '../../contexts';
import {useChannels} from '../../hooks';
import {NoSourcePlaceholder} from './ColumnDetailsPlaceholder';
import {SourceFooter} from './SourceFooter';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 400px;
  display: flex;
  flex-direction: column;
  padding: 0px 4px;
  display: flex;
  flex-direction: column;
`;

const Content = styled.div`
  flex: 1;
`;

const SourcesSectionTitle = styled(SectionTitle)`
  z-index: 10;
`;

type ColumnDetailsProps = {
  columnConfiguration: ColumnConfiguration;
  onColumnChange: (column: ColumnConfiguration) => void;
};

const ColumnDetails = ({columnConfiguration, onColumnChange}: ColumnDetailsProps) => {
  const translate = useTranslate();
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

  useEffect(() => {
    switchTo(firstSource);
  }, [switchTo, firstSource]);

  const attributeFetcher = useFetchers().attribute;
  const channels = useChannels();

  const handleSourceAdd = async (addedSourceCode: string, sourceType: string) => {
    if (sourceType === 'property') {
      const updatedColumnConfiguration = addPropertySource(columnConfiguration, addedSourceCode);
      onColumnChange(updatedColumnConfiguration);
      switchTo(updatedColumnConfiguration.sources[updatedColumnConfiguration.sources.length - 1]?.uuid ?? '');
    } else {
      const [attribute] = await attributeFetcher.fetchByIdentifiers([addedSourceCode]);
      const updatedColumnConfiguration = addAttributeSource(columnConfiguration, addedSourceCode, attribute, channels);
      onColumnChange(updatedColumnConfiguration);
      switchTo(updatedColumnConfiguration.sources[updatedColumnConfiguration.sources.length - 1]?.uuid ?? '');
    }
  };

  const sourcesErrors = useValidationErrors(`[columns][${columnConfiguration.uuid}][sources]`, true);
  const validationErrors = useValidationErrors(`[columns][${columnConfiguration.uuid}][sources]`, false);


  return (
    <Container>
      <SourcesSectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_export.column_details.sources.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddSourceDropdown onSourceSelected={handleSourceAdd} />
      </SourcesSectionTitle>
      <Content>
        {columnConfiguration.sources.length !== 0 && (
          <SourceTabBar sources={columnConfiguration.sources} currentTab={currentSourceUuid} onTabChange={switchTo} />
        )}
        {sourcesErrors.map((error, index) => (
          <Helper key={index} level="error">
            {translate(error.messageTemplate, error.parameters)}
          </Helper>
        ))}
        {currentSource && (
          <SourceConfigurator
            source={currentSource}
            validationErrors={filterErrors(validationErrors, `[${currentSource.uuid}]`)}
            onSourceChange={handleSourceChange}
          />
        )}
        {currentSource && <SourceFooter source={currentSource} onSourceRemove={handleSourceRemove} />}
        {columnConfiguration.sources.length === 0 && <NoSourcePlaceholder />}
      </Content>
    </Container>
  );
};

export {ColumnDetails};
