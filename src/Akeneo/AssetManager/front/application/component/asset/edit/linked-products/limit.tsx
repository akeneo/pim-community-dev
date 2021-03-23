import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {Scroll} from 'akeneoassetmanager/application/component/app/illustration/scroll';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {Button} from 'akeneoassetmanager/application/component/app/button';
import {getProductIndexUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {updateDatagridStateWithFilterOnAssetCode} from 'akeneoassetmanager/tools/datagridstate';

const Container = styled.div`
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const LimitTitle = styled.span`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  white-space: nowrap;
`;

const LimitSubtitle = styled.span`
  margin: 10px 0 20px;
`;

const StyledButton = styled(Button)`
  margin-bottom: 20px;
`;

type LimitProps = {
  assetCode: string;
  productCount: number;
  totalCount: number;
  attribute: NormalizedAttribute | null;
};

export const Limit = ({assetCode, productCount, totalCount, attribute}: LimitProps) => {
  if (null === attribute || productCount >= totalCount) return null;

  return (
    <Container>
      <LimitTitle>
        {__('pim_asset_manager.asset.product.not_enough_items.title', {productCount, totalCount})}
      </LimitTitle>
      <LimitSubtitle>
        {__(
          `pim_asset_manager.asset.product.not_enough_items.subtitle.${
            attribute.useable_as_grid_filter ? 'usable_in_grid' : 'not_usable_in_grid'
          }`
        )}
      </LimitSubtitle>
      {attribute.useable_as_grid_filter && (
        <a href={getProductIndexUrl()}>
          <StyledButton
            tabIndex="0"
            role="button"
            onClick={() => updateDatagridStateWithFilterOnAssetCode(attribute.code, assetCode)}
            color="green"
          >
            {__('pim_asset_manager.asset.product.not_enough_items.button')}
          </StyledButton>
        </a>
      )}
      <Scroll />
    </Container>
  );
};
