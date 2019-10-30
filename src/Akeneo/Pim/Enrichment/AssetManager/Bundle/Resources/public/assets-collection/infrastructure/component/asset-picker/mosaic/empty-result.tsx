import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import AssetIllustration from 'akeneopimenrichmentassetmanager/platform/component/visual/illustration/asset';

const EmptyContainer = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  justify-content: center;
`;
const Title = styled.div`
  margin-bottom: 10px;
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
`;
const SubTitle = styled.div`
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.bigger};
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
`;

const EmptyResult = () => {
  return (
    <EmptyContainer>
      <AssetIllustration size={256} />
      <Title>{__('pim_asset_manager.asset_picker.no_result.title')}</Title>
      <SubTitle>{__('pim_asset_manager.asset_picker.no_result.sub_title')}</SubTitle>
    </EmptyContainer>
  );
};

export default EmptyResult;
