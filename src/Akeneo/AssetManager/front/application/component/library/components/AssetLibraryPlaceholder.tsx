import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import React from 'react';
import styled from 'styled-components';

const AssetCardPlaceholderGrid = styled.div`
  margin-top: 20px;
  display: grid;
  grid-gap: 20px;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
`;

const AssetCardPlaceholder = styled.div`
  width: 100%;
  padding-top: 100%; /* 1:1 Aspect Ratio */
  position: relative;
  margin-bottom: 6px;
  min-height: 140px;
`;

const SearchBarPlaceholder = styled.div`
  height: 45px;
  width: 100%;
`;

const AssetLibraryPlaceholder = ({assetFamily}: {assetFamily: AssetFamily | null}) => {
  return (
    <>
      <div className={`AknLoadingPlaceHolderContainer`}>
        <SearchBarPlaceholder />
      </div>
      <AssetCardPlaceholderGrid className={`AknLoadingPlaceHolderContainer`}>
        {undefined !== assetFamily?.assetCount &&
          [...Array(Math.min(assetFamily.assetCount, 50))].map((_e, i) => <AssetCardPlaceholder key={i} />)}
      </AssetCardPlaceholderGrid>
    </>
  );
};

export {AssetLibraryPlaceholder};
