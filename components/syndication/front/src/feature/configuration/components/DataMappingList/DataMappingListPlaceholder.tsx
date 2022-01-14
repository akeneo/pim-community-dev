import React from 'react';
import styled from 'styled-components';
import {Button, Link, AttributesIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  margin-top: 40px;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
`;

const Subtitle = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  text-align: center;
`;

type DataMappingListPlaceholderProps = {
  onDataMappingCreated: (target: string) => void;
};

const DataMappingListPlaceholder = ({onDataMappingCreated}: DataMappingListPlaceholderProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <AttributesIllustration size={256} />
      <Title>{translate('akeneo.syndication.data_mapping_list.no_data_mapping_selection.title')}</Title>
      <Subtitle>
        {translate('akeneo.syndication.data_mapping_list.no_data_mapping_selection.subtitle')}{' '}
        <Link
          target="_blank"
          href="https://help.akeneo.com/pim/serenity/articles/syndication.html#define-your-export-structure"
        >
          {translate('akeneo.syndication.data_mapping_list.no_data_mapping_selection.link')}
        </Link>
      </Subtitle>
      <Button level="secondary" ghost={true} onClick={() => onDataMappingCreated('')}>
        {translate('akeneo.syndication.data_mapping_list.no_data_mapping_selection.add_data_mapping')}
      </Button>
    </Container>
  );
};

export {DataMappingListPlaceholder};
