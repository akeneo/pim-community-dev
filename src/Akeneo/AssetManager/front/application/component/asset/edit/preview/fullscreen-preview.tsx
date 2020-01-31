import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {Modal, Title} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import {useShortcut} from 'akeneoassetmanager/application/hooks/input';
import Key from 'akeneoassetmanager/tools/key';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {PreviewModel} from 'akeneoassetmanager/domain/model/asset/value';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
`;

type FullscreenPreviewProps = {
  anchor: React.ComponentType<any>;
  label: string;
  previewModel: PreviewModel | undefined;
  attribute: NormalizedAttribute;
  children?: React.ReactNode;
};

export const FullscreenPreview = ({
  anchor: Anchor,
  label,
  previewModel,
  attribute,
  children,
}: FullscreenPreviewProps) => {
  const [isModalOpen, setModalOpen] = React.useState(false);

  const openModal = React.useCallback(() => setModalOpen(true), []);
  const closeModal = React.useCallback(() => setModalOpen(false), []);

  useShortcut(Key.Escape, closeModal);

  return (
    <>
      <Anchor onClick={openModal}>{children}</Anchor>
      {isModalOpen && (
        <Modal>
          <Container>
            <CloseButton title={__('pim_asset_manager.close')} onClick={closeModal} />
            <Title>{label}</Title>
            <MediaPreview label={label} previewModel={previewModel} attribute={attribute} />
          </Container>
        </Modal>
      )}
    </>
  );
};
