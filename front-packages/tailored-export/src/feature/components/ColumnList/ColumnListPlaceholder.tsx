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

type ColumnListPlaceholderProps = {
  onColumnCreated: (target: string) => void;
};

const ColumnListPlaceholder = ({onColumnCreated}: ColumnListPlaceholderProps) => {
  const translate = useTranslate();

  return (
    <Container>
      <AttributesIllustration size={256} />
      <Title>{translate('akeneo.tailored_export.column_list.no_column_selection.title')}</Title>
      <Subtitle>
        {translate('akeneo.tailored_export.column_list.no_column_selection.subtitle')}{' '}
        <Link href={'#TODO'}>{translate('akeneo.tailored_export.column_list.no_column_selection.link')}</Link>
      </Subtitle>
      <Button level="secondary" ghost={true} onClick={() => onColumnCreated('')}>
        {translate('akeneo.tailored_export.column_list.no_column_selection.add_column')}
      </Button>
    </Container>
  );
};

export {ColumnListPlaceholder};
