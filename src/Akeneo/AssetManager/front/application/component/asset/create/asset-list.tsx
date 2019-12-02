import * as React from 'react';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {Spacer} from 'akeneoassetmanager/application/component/app/spacer';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';

type AssetListProps = {
  assets: any[];
  onAssetRemove: () => void;
};

const Header = styled.div`
  display: flex;
  border-bottom: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey140};
  padding-bottom: 7px;
  align-items: center;
`;
const AssetCount = styled.div`
  text-transform: uppercase;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.big};
  font-weight: normal;
`;
const ActionButton = styled(Button)`
  margin-left: 10px;
`;

const AssetList = ({assets}: AssetListProps) => {
  return (
    <>
      <Header>
        <AssetCount>
          {__('pim_asset_manager.asset.create.asset_count', {count: assets.length}, assets.length)}
        </AssetCount>
        <Spacer />
        <ActionButton color="outline">{__('pim_asset_manager.asset.create.add_new')}</ActionButton>
        <ActionButton color="outline">{__('pim_asset_manager.asset.create.remove_all')}</ActionButton>
      </Header>
    </>
  );
};

export default AssetList;
