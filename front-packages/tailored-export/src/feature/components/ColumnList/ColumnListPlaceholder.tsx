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
      <Title>{translate('No columns selection to export')}</Title>
      <Subtitle>
        {translate('You must define your columns selection in order to export. If you don’t know how, ')}
        <Link href={'#'}>{translate('take a look at this article.')}</Link>
      </Subtitle>
      <Button
        onClick={() => {
          onColumnCreated('');
        }}
      >
        {translate('Add first column')}
      </Button>
    </Container>
  );
};

export {ColumnListPlaceholder};
