import React from 'react';
import styled from 'styled-components';
import {Image} from 'akeneo-design-system';
import {useRouter} from '@akeneo-pim-community/shared';
import {Attribute, File} from '../../../models';
import {getMediaPreviewUrl} from '../../../tools/media-url-generator';
import {MediaPreviewType} from '../../../models/MediaPreview';

const PreviewImage = styled(Image)`
  border: none;
  width: auto;
  min-height: 250px;
  max-width: 100%;
  max-height: calc(100vh - 250px);
`;

type MediaPreviewProps = {
  label: string;
  data: File;
  attribute: Attribute;
};

const MediaPreview = ({data, label, attribute}: MediaPreviewProps) => {
  const router = useRouter();
  const previewUrl = getMediaPreviewUrl(router, {
    type: MediaPreviewType.Thumbnail,
    attributeCode: attribute.code,
    data: data && data.filePath ? data.filePath : '',
  });

  return (
    <>
      <PreviewImage fit="contain" src={previewUrl} alt={label} />
    </>
  );
};

export {MediaPreview};
