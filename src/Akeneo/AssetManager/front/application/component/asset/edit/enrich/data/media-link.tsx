import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import MediaLinkData, {create} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {ConcreteMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import Key from 'akeneoassetmanager/tools/key';
import {
  copyToClipboard,
  getMediaLinkPreviewUrl,
  getMediaLinkUrl,
  MediaPreviewTypes
} from 'akeneoassetmanager/tools/media-url-generator';
import styled, {ThemeProvider} from 'styled-components';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import DownloadIcon from 'akeneoassetmanager/application/component/app/icon/download';
import LinkIcon from 'akeneoassetmanager/application/component/app/icon/link';

const Container = styled.div`
  align-items: center;
  border-radius: 2px;
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey80};
  display: flex;
  flex: 1;
  justify-content: center;
  max-width: 460px;
  padding: 12px;
`;

const Thumbnail = styled.img`
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey60};
  flex-shrink: 0;
  height: 42px;
  margin-right: 10px;
  object-fit: cover;
  width: 42px;
`;

const ActionLink = styled.a`
  align-items: center;
  cursor: pointer;
  display: flex;
  flex-shrink: 0;
  height: 28px;
  justify-content: center;
  margin: 0 2px;
  width: 28px;
`;

const ActionButton = styled.button`
  background: none;
  border: none;
  cursor: pointer;
  display: block;
  flex-shrink: 0;
  height: 28px;
  margin: 0 2px;
  width: 28px;
`;

const View = ({
  value,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: Value;
  onChange: (value: Value) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!(value.data instanceof MediaLinkData && value.attribute instanceof ConcreteMediaLinkAttribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = create(text);
    if (newData.equals(value.data)) {
      return;
    }

    const newValue = value.setData(newData);

    onChange(newValue);
  };

  const mediaDownloadUrl = getMediaLinkUrl(value.data, value.attribute);
  const mediaPreviewUrl = getMediaLinkPreviewUrl(MediaPreviewTypes.ThumbnailSmall, value.data, value.attribute);

  // !TODO remove <ThemeProvider> when it will be implemented in one of the parents
  return (
    <ThemeProvider theme={akeneoTheme}>
      <Container>
        <Thumbnail
          src={mediaPreviewUrl}
          alt={__('pim_asset_manager.attribute.media_type_preview')}
        />
        <input
          id={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
          autoComplete="off"
          className={`AknTextField AknTextField--light
        ${value.attribute.valuePerLocale ? 'AknTextField--localizable' : ''}
        ${!canEditData ? 'AknTextField--disabled' : ''}`}
          value={value.data.stringValue()}
          onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
            onValueChange(event.currentTarget.value);
          }}
          onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
            if (Key.Enter === event.key) onSubmit();
          }}
          disabled={!canEditData}
          readOnly={!canEditData}
        />
        <ActionLink
          href={mediaDownloadUrl}
          target="_blank"
          title={__('pim_asset_manager.media_link.download')}
        >
          <DownloadIcon
            color={akeneoTheme.color.grey100}
            size={20}
            title={__('pim_asset_manager.media_link.download')}
          />
        </ActionLink>
        <ActionButton
          title={__('pim_asset_manager.media_link.copy')}
          onClick={() => {
            copyToClipboard(mediaDownloadUrl);
          }}
        >
          <LinkIcon
            color={akeneoTheme.color.grey100}
            size={20}
            title={__('pim_asset_manager.media_link.copy')}
          />
        </ActionButton>
      </Container>
    </ThemeProvider>
  );
};

export const view = View;
