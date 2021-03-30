import React from "react";
import {
  Button,
  DownloadIcon,
  FullscreenIcon, getColor,
  IconButton,
  MediaFileInput,
  useBooleanState
} from "akeneo-design-system";
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import { FullscreenPreview } from "akeneo-design-system/lib/storybook";
import styled from "styled-components";

const AddedClassName = 'ImageCard--added';
const RemovedClassName = 'ImageCard--removed';
const ImageCardWrapper = styled.div`
  margin-top: 5px;
  
  & > .${AddedClassName} {
    background: ${getColor('green', 20)};
  }
  & > .${RemovedClassName} {
    background: ${getColor('red', 20)};
  }
`

type ImageCardProps = {
  thumbnailUrl?: string;
  filePath: string;
  originalFilename: string;
  downloadUrl: string;
  state?: 'removed' | 'added'
}

const ImageCard: React.FC<ImageCardProps> = ({
  thumbnailUrl = '/bundles/pimui/img/image_default.png',
  filePath,
  originalFilename,
  downloadUrl,
  state,
  ...rest
}) => {
  const [isFullscreenModalOpen, openFullscreenModal, closeFullscreenModal] = useBooleanState();
  const translate = useTranslate();

  const className = {
    removed: RemovedClassName,
    added: AddedClassName,
  }[state];

  return <>
    <ImageCardWrapper>
      <MediaFileInput
        clearTitle={''}
        uploader={''}
        uploadErrorLabel={''}
        uploadingLabel={''}
        size="small"
        thumbnailUrl={thumbnailUrl}
        value={{filePath, originalFilename}}
        clearable={false}
        className={className}
        {...rest}
      >
        <IconButton
          download={downloadUrl}
          href={downloadUrl}
          icon={<DownloadIcon />}
          target="_blank"
          title={translate('pim_asset_manager.asset_preview.download')}
        />
        {thumbnailUrl !== '/bundles/pimui/img/image_default.png' &&
        <IconButton
          icon={<FullscreenIcon/>}
          onClick={openFullscreenModal}
          title={translate('pim_asset_manager.asset.button.fullscreen')}
        />
      }
      </MediaFileInput>
    </ImageCardWrapper>
    {thumbnailUrl !== '/bundles/pimui/img/image_default.png' && isFullscreenModalOpen &&
    <FullscreenPreview title={originalFilename} src={thumbnailUrl} onClose={closeFullscreenModal}>
      <Button href={thumbnailUrl} ghost={true} level="tertiary" target="_blank" download={thumbnailUrl}>
        <DownloadIcon /> {translate('pim_asset_manager.asset_preview.download')}
      </Button>
    </FullscreenPreview>
    }
  </>
}

export { ImageCard }
