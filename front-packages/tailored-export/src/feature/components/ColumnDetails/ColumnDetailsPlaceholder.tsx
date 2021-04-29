import React from 'react';
import styled from 'styled-components';
import {getColor, getFontSize, RulesIllustration, Link} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 60px;
  padding: 20px;
  gap: 10px;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('big')};
`;

const SubTitle = styled.div`
  color: ${getColor('grey', 100)};
  font-size: ${getFontSize('default')};
  text-align: center;
`;

const ColumnDetailsPlaceholder = () => {
  const translate = useTranslate();

  return (
    <Container>
      <RulesIllustration size={128} />
      <Title>{translate('No source selected for the moment.')}</Title>
      <SubTitle>
        {translate('To know more about mappping and operations, ')}
        <Link href={'#'}>{translate('this article may help you')}</Link>
      </SubTitle>
    </Container>
  );
};

const NoSelectedColumn = () => {
  const translate = useTranslate();

  return (
    <Container>
      <RulesIllustration size={128} />
      <Title>{translate('No column selected for the moment.')}</Title>
    </Container>
  );
};

export {ColumnDetailsPlaceholder, NoSelectedColumn};
