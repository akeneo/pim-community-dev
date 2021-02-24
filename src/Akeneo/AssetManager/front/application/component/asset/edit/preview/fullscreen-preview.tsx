import React from 'react';
import styled from 'styled-components';
import {getColor, Modal, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  Actions,
  DownloadAction,
  CopyUrlAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {MediaData} from 'akeneoassetmanager/domain/model/asset/data';

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

type FullscreenPreviewProps = {
  anchor: React.ComponentType<any>;
  label: string;
  attribute: NormalizedAttribute;
  data: MediaData;
  children?: React.ReactNode;
};

const FullscreenPreview = ({anchor: Anchor, label, data, attribute, children}: FullscreenPreviewProps) => {
  const translate = useTranslate();
  const [isModalOpen, openModal, closeModal] = useBooleanState();

  return (
    <>
      <Anchor onClick={openModal}>{children}</Anchor>
      {isModalOpen && (
        <Modal onClose={closeModal} closeTitle={translate('pim_common.close')}>
          <BrandedTitle>{label}</BrandedTitle>
          <Border>
            <MediaPreview label={label} data={data} attribute={attribute} />
            <Actions margin={20}>
              <CopyUrlAction
                data={data}
                attribute={attribute}
                label={translate('pim_asset_manager.asset_preview.copy_url')}
              />
              <DownloadAction
                data={data}
                attribute={attribute}
                label={translate('pim_asset_manager.asset_preview.download')}
              />
            </Actions>
          </Border>
        </Modal>
      )}
    </>
  );
};

export {FullscreenPreview};
