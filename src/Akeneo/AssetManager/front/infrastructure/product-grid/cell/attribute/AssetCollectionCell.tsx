import React from 'react';
import styled, {ThemeProvider} from 'styled-components';
import {Image, pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {useRouter} from '@akeneo-pim-community/shared';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {getMediaData, MediaData} from 'akeneoassetmanager/domain/model/asset/data';

const AssetCollectionImage = styled(Image)`
  margin-top: 3px;
  margin-bottom: -3px;
`;

type AssetCollectionCellProps = {
  attributeIdentifier: string;
  data: MediaData;
};

const ImageCell = ({attributeIdentifier, data}: AssetCollectionCellProps) => {
  const router = useRouter();

  const src = getMediaPreviewUrl(router, {
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier,
    data: getMediaData(data),
  });

  return <AssetCollectionImage width={44} height={44} src={src} alt="" />;
};

const AssetCollectionCell = (props: AssetCollectionCellProps) => (
  <DependenciesProvider>
    <ThemeProvider theme={pimTheme}>
      <ImageCell {...props} />
    </ThemeProvider>
  </DependenciesProvider>
);

export {AssetCollectionCell};
