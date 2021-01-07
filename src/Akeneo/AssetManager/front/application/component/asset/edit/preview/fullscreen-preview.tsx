import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {Modal, Title} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {
  Actions,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {MediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {Key, useShortcut} from 'akeneo-design-system';

const Container = styled.div`
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
`;

export const PreviewContainer = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
`;

export const Border = styled.div`
  display: flex;
  flex-direction: column;
  padding: 20px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  max-height: 100%;

  > :first-child {
    margin-bottom: 20px;
  }
`;

type FullscreenPreviewProps = {
  anchor: React.ComponentType<any>;
  label: string;
  attribute: NormalizedAttribute;
  data: MediaData;
  children?: React.ReactNode;
};

export const FullscreenPreview = ({anchor: Anchor, label, data, attribute, children}: FullscreenPreviewProps) => {
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
            <PreviewContainer>
              <Border>
                <MediaPreview label={label} data={data} attribute={attribute} />
                <Actions margin={20}>
                  <CopyUrlAction
                    data={data}
                    attribute={attribute}
                    label={__('pim_asset_manager.asset_preview.copy_url')}
                  />
                  <DownloadAction
                    data={data}
                    attribute={attribute}
                    label={__('pim_asset_manager.asset_preview.download')}
                  />
                </Actions>
              </Border>
            </PreviewContainer>
          </Container>
        </Modal>
      )}
    </>
  );
};
