import React from 'react';
import styled from 'styled-components';
import {Button, ButtonProps, DownloadIcon, getColor, Modal} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {MediaPreview} from './media-preview';
import {Attribute} from '../../../models';
import {File} from '../../../models';
import {getImageDownloadUrl, getMediaPreviewUrl} from '../../../tools/media-url-generator';
import {MediaPreviewType} from '../../../models/MediaPreview';

const Border = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  border: 1px solid ${getColor('grey', 80)};
  max-height: 100%;
  gap: 20px;
`;

const BrandedTitle = styled(Modal.Title)`
  color: ${getColor('brand', 100)};
`;

const ButtonContainer = styled.div`
  display: flex;
  gap: 10px;
  align-items: center;
`;

type FullscreenPreviewProps = {
  label: string;
  attribute: Attribute;
  data: File;
  onClose: () => void;
};

const buttonProps: Partial<ButtonProps> = {
  level: 'tertiary',
  ghost: true,
};

const FullscreenPreview = ({label, data, attribute, onClose}: FullscreenPreviewProps) => {
  const translate = useTranslate();
  const router = useRouter();

  const downloadUrl = data ? getImageDownloadUrl(router, data) : '';
  const previewUrl = getMediaPreviewUrl(router, {
    type: MediaPreviewType.Preview,
    attributeCode: attribute.code,
    data: data && data.filePath ? data.filePath : '',
  });

  return (
    <Modal onClose={onClose} closeTitle={translate('pim_common.close')}>
      <BrandedTitle>{label}</BrandedTitle>
      <Border>
        <MediaPreview previewUrl={previewUrl} label={label} />
        {downloadUrl && data && data.originalFilename && (
          <ButtonContainer>
            <Button {...buttonProps} href={downloadUrl} download={data.originalFilename} target="_blank">
              <DownloadIcon />
              Download
            </Button>
          </ButtonContainer>
        )}
      </Border>
    </Modal>
  );
};

export {FullscreenPreview};
