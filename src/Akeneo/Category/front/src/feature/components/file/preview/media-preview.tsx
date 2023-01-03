import React from 'react';
import styled from 'styled-components';
import {Image} from 'akeneo-design-system';

const PreviewImage = styled(Image)`
  border: none;
  width: auto;
  min-height: 250px;
  max-width: 100%;
  max-height: calc(100vh - 250px);
`;

type MediaPreviewProps = {
  previewUrl: string;
  label: string;
};

const MediaPreview = ({previewUrl, label}: MediaPreviewProps) => {
  return (
    <>
      <PreviewImage fit="contain" src={previewUrl} alt={label} />
    </>
  );
};

export {MediaPreview};
