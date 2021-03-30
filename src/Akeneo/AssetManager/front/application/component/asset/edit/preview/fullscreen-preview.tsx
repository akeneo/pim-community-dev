import React from 'react';
import styled from 'styled-components';
import {getColor, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {MediaPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/media-preview';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {DownloadAction, CopyUrlAction} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {MediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {ButtonContainer} from 'akeneoassetmanager/application/component/app/button';

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

type FullscreenPreviewProps = {
  label: string;
  attribute: NormalizedAttribute;
  data: MediaData;
  onClose: () => void;
};

const FullscreenPreview = ({label, data, attribute, onClose}: FullscreenPreviewProps) => {
  const translate = useTranslate();

  return (
    <Modal onClose={onClose} closeTitle={translate('pim_common.close')}>
      <BrandedTitle>{label}</BrandedTitle>
      <Border>
        <MediaPreview label={label} data={data} attribute={attribute} />
        <ButtonContainer>
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
        </ButtonContainer>
      </Border>
    </Modal>
  );
};

export {FullscreenPreview};
