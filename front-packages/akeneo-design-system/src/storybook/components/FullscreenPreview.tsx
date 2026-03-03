import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Image, Modal} from '../../components';
import {getColor} from '../../theme';

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

const Actions = styled.div`
  display: flex;
  justify-content: center;
  gap: 10px;
`;

const PreviewImage = styled(Image)`
  width: auto;
  min-height: 250px;
  max-width: 100%;
  object-fit: contain;
  max-height: calc(-250px + 100vh);
`;

type FullscreenPreviewProps = {
  title: string;
  src: string;
  onClose: () => void;
  children: ReactNode;
};

const FullscreenPreview = ({title, src, onClose, children}: FullscreenPreviewProps) => {
  return (
    <Modal onClose={onClose} closeTitle="Close">
      <BrandedTitle>{title}</BrandedTitle>
      <Border>
        <PreviewImage src={src} alt={title} />
        <Actions>{children}</Actions>
      </Border>
    </Modal>
  );
};

export {FullscreenPreview};
