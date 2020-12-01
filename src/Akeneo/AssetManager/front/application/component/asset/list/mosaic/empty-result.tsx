import React from 'react';
import styled from 'styled-components';
import {AssetsIllustration, getColor, getFontSize} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

const EmptyContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  justify-content: center;
`;
const Title = styled.div`
  margin-bottom: 10px;
  font-size: ${getFontSize('title')};
  color: ${getColor('grey', 140)};
`;
const SubTitle = styled.div`
  font-size: ${getFontSize('bigger')};
  color: ${getColor('grey', 120)};
`;

const EmptyResult = () => {
  const translate = useTranslate();

  return (
    <EmptyContainer>
      <AssetsIllustration size={256} />
      <Title>{translate('pim_asset_manager.asset_picker.no_result.title')}</Title>
      <SubTitle>{translate('pim_asset_manager.asset_picker.no_result.sub_title')}</SubTitle>
    </EmptyContainer>
  );
};

export default EmptyResult;
