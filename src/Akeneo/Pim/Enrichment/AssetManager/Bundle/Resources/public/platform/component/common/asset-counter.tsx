import * as React from 'react';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoassetmanager/tools/translator';

const Container = styled.div`
  white-space: nowrap;
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-left: 10px;
  line-height: 16px;
`;

type AssetCounterProps = {
  resultCount: number | null;
};

const AssetCounter = ({resultCount = null}: AssetCounterProps) =>
  resultCount === null ? null : (
    <Container>{__('pim_asset_manager.asset_collection.asset_count', {resultCount}, resultCount)}</Container>
  );

export default AssetCounter;
