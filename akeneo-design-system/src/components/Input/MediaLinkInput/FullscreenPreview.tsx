import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Image} from '../../../components';
import {Modal} from '../../Modal/Modal';
import {getColor} from '../../../theme';

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

type FullscreenPreviewProps = {
  closeTitle: string;
  onClose: () => void;
  children: ReactNode;
};

const FullscreenPreview = ({closeTitle, onClose, children}: FullscreenPreviewProps) => {
  return (
    <Modal onClose={onClose} closeTitle={closeTitle}>
      {children}
    </Modal>
  );
};

FullscreenPreview.Content = Border;
FullscreenPreview.Title = BrandedTitle;
FullscreenPreview.Actions = Actions;
FullscreenPreview.Image = Image;

export {FullscreenPreview};
