import React from 'react';
import styled from 'styled-components';
import {Card, CardGrid, SkeletonPlaceholder} from 'akeneo-design-system';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';

const SearchBarPlaceholder = styled(SkeletonPlaceholder)`
  height: 45px;
  width: 100%;
  margin-bottom: 20px;
`;

type AssetLibraryPlaceholderProps = {
  assetFamily: AssetFamily | null;
};

const AssetLibraryPlaceholder = ({assetFamily}: AssetLibraryPlaceholderProps) => (
  <>
    <SearchBarPlaceholder>{assetFamily?.code}</SearchBarPlaceholder>
    <CardGrid>
      {undefined !== assetFamily?.assetCount &&
        [...Array(Math.min(assetFamily.assetCount, 50))].map((_e, i) => (
          <Card key={i} src={null}>
            {i}
          </Card>
        ))}
    </CardGrid>
  </>
);

export {AssetLibraryPlaceholder};
