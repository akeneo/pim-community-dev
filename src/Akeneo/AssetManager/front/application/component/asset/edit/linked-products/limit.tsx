import React from 'react';
import styled from 'styled-components';
import {UsingIllustration, Button, getFontSize, getColor} from 'akeneo-design-system';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import {updateDatagridStateWithFilterOnAssetCode} from 'akeneoassetmanager/tools/datagridstate';
import {useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Container = styled.div`
  margin-top: 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const LimitTitle = styled.span`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  white-space: nowrap;
`;

const LimitSubtitle = styled.span`
  margin: 10px 0 20px;
`;

type LimitProps = {
  assetCode: string;
  productCount: number;
  totalCount: number;
  attribute: NormalizedAttribute | null;
};

const Limit = ({assetCode, productCount, totalCount, attribute}: LimitProps) => {
  const translate = useTranslate();
  const productIndexRoute = useRoute('pim_enrich_product_index');

  if (null === attribute || productCount >= totalCount) return null;

  return (
    <Container>
      <LimitTitle>
        {translate('pim_asset_manager.asset.product.not_enough_items.title', {productCount, totalCount})}
      </LimitTitle>
      <LimitSubtitle>
        {translate(
          `pim_asset_manager.asset.product.not_enough_items.subtitle.${
            attribute.useable_as_grid_filter ? 'usable_in_grid' : 'not_usable_in_grid'
          }`
        )}
      </LimitSubtitle>
      {attribute.useable_as_grid_filter && (
        <Button
          href={`#${productIndexRoute}`}
          target="_blank"
          onClick={() => updateDatagridStateWithFilterOnAssetCode(attribute.code, assetCode)}
        >
          {translate('pim_asset_manager.asset.product.not_enough_items.button')}
        </Button>
      )}
      <UsingIllustration />
    </Container>
  );
};

export {Limit};
