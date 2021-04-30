import React from 'react';
import styled from 'styled-components';
import {Button, Link, AttributesIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 128px;
  padding: 20px;
  gap: 10px;
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
        <Link href={'#'}>{translate('akeneo.tailored_export.column_list.no_column_selection.link')}</Link>
      </Subtitle>
      <Button onClick={() => onColumnCreated('')}>
        {translate('akeneo.tailored_export.column_list.no_column_selection.add_column')}
      </Button>
    </Container>
  );
};

export {ColumnListPlaceholder};
