import React from 'react';
import styled from 'styled-components';
import {Placeholder, RulesIllustration, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 60px;
`;

const DataMappingDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_import.data_mapping.title')}</SectionTitle.Title>
      </SectionTitle>
      <Placeholder
        illustration={<RulesIllustration />}
        title={translate('akeneo.tailored_import.data_mapping.no_data_mapping_selected')}
      />
    </Container>
  );
};

export {DataMappingDetailsPlaceholder};
