import React from 'react';
import styled from 'styled-components';
import {IconButton, Image} from '../../../components';
import {Modal} from '../../Modal/Modal';
import {getColor} from '../../../theme';
import {DownloadIcon} from '../../../icons';
import {FileInfo} from './FileInfo';

const Border = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid ${getColor('grey', 80)};
  max-height: 100%;
  gap: 20px;
`;

const BrandedTitle = styled(Modal.Title)`
  color: ${getColor('brand', 100)};
`;

const ActionButton = styled(IconButton)`
  color: ${getColor('grey', 100)};
`;

const Actions = styled.div``;

type FullscreenPreviewProps = {
  value: FileInfo;
  previewUrl: string;
  downloadUrl: string;
  downloadTitle: string;
  closeTitle: string;
  label: string;
  onClose: () => void;
};

const FullscreenPreview = ({
  value,
  previewUrl,
  downloadUrl,
  downloadTitle,
  closeTitle,
  label,
  onClose,
}: FullscreenPreviewProps) => {
  return (
    <Modal onClose={onClose} closeTitle={closeTitle}>
      <BrandedTitle>{label}</BrandedTitle>
      <Border>
        <Image src={previewUrl} alt={label} />
        <Actions>
          <ActionButton
            href={downloadUrl}
            download={value.originalFilename}
            level="tertiary"
            ghost="borderless"
            icon={<DownloadIcon size={16} />}
            title={downloadTitle}
          />
        </Actions>
      </Border>
    </Modal>
  );
};

export {FullscreenPreview};
