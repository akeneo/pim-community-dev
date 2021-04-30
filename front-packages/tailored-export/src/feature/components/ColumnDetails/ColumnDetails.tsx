import React from 'react';
import styled from 'styled-components';
import {ColumnConfiguration} from '../../models/ColumnConfiguration';
import {ColumnDetailsPlaceholder, NoSelectedColumn} from './ColumnDetailsPlaceholder';
import {Button, SectionTitle} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  height: 100%;
  overflow-y: auto;
  width: 400px;
  display: flex;
  flex-direction: column;
`;

const Content = styled.div`
  flex: 1;
`;

type ColumnDetailsProps = {
  columnConfiguration: ColumnConfiguration | null;
  noColumns: boolean;
  onColumnChange: (column: ColumnConfiguration) => void;
};

const ColumnDetails = ({columnConfiguration, noColumns}: ColumnDetailsProps) => {
  const translate = useTranslate();
  return (
    <Container>
      <SectionTitle sticky={0}>
        <SectionTitle.Title>{translate('akeneo.tailored_export.column_details.sources.title')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <Button disabled={columnConfiguration === null}>
          {translate('akeneo.tailored_export.column_details.sources.add')}
        </Button>
      </SectionTitle>
      <Content>
        {(null === columnConfiguration || columnConfiguration.sources.length === 0) && !noColumns && (
          <ColumnDetailsPlaceholder />
        )}
        {noColumns && <NoSelectedColumn />}
        <div>{columnConfiguration && columnConfiguration.uuid}</div>
      </Content>
    </Container>
  );
};

export {ColumnDetails};
