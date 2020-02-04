import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import Key from 'akeneoassetmanager/tools/key';
import {copyToClipboard, getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import styled from 'styled-components';
import {akeneoTheme, ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import DownloadIcon from 'akeneoassetmanager/application/component/app/icon/download';
import {Copy} from 'akeneoassetmanager/application/component/app/icon/copy';
import {
  isMediaLinkData,
  mediaLinkDataFromString,
  areMediaLinkDataEqual,
  mediaLinkDataStringValue,
  getMediaLinkUrl,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {setValueData, isValueEmpty, getPreviewModelFromValue} from 'akeneoassetmanager/domain/model/asset/value';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference from 'akeneoassetmanager/domain/model/channel-reference';
import {Fullscreen} from 'akeneoassetmanager/application/component/app/icon/fullscreen';
import {TransparentButton, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {Link} from 'akeneoassetmanager/application/component/app/link';

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
  width: 40px;
  height: 40px;
  margin-right: 10px;
  object-fit: cover;
`;

const StyledButtonContainer = styled(ButtonContainer)`
  > :not(:first-child) {
    margin-left: unset;
  }

  > ${Link}, > ${TransparentButton} {
    display: flex;
    margin-left: 12px;
  }
`;

const View = ({
  value,
  channel,
  locale,
  onChange,
  onSubmit,
  canEditData,
}: {
  value: EditionValue;
  channel: ChannelReference;
  locale: LocaleReference;
  onChange: (value: EditionValue) => void;
  onSubmit: () => void;
  canEditData: boolean;
}) => {
  if (!isMediaLinkData(value.data) || !isMediaLinkAttribute(value.attribute)) {
    return null;
  }

  const onValueChange = (text: string) => {
    const newData = mediaLinkDataFromString(text);
    if (areMediaLinkDataEqual(newData, value.data)) {
      return;
    }

    const newValue = setValueData(value, newData);

    onChange(newValue);
  };

  const mediaDownloadUrl = getMediaLinkUrl(value.data, value.attribute);
  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.Thumbnail,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  const label = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );
  const previewModel = getPreviewModelFromValue(value, channel, locale);

  return (
    <Container>
      <Thumbnail src={mediaPreviewUrl} alt={__('pim_asset_manager.attribute.media_type_preview')} />
      <input
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        autoComplete="off"
        className={`AknTextField AknTextField--light
        ${value.attribute.value_per_locale ? 'AknTextField--localizable' : ''}
        ${!canEditData ? 'AknTextField--disabled' : ''}`}
        value={mediaLinkDataStringValue(value.data)}
        onChange={(event: React.ChangeEvent<HTMLInputElement>) => {
          onValueChange(event.currentTarget.value);
        }}
        onKeyDown={(event: React.KeyboardEvent<HTMLInputElement>) => {
          if (Key.Enter === event.key) onSubmit();
        }}
        disabled={!canEditData}
        readOnly={!canEditData}
      />
      {!isValueEmpty(value) && (
        <StyledButtonContainer>
          {MediaTypes.youtube !== value.attribute.media_type && (
            <Link href={mediaDownloadUrl} target="_blank" title={__('pim_asset_manager.media_link.download')}>
              <DownloadIcon
                color={akeneoTheme.color.grey100}
                size={20}
                title={__('pim_asset_manager.media_link.download')}
              />
            </Link>
          )}
          <TransparentButton
            title={__('pim_asset_manager.media_link.copy')}
            onClick={() => copyToClipboard(mediaDownloadUrl)}
          >
            <Copy color={akeneoTheme.color.grey100} size={20} title={__('pim_asset_manager.media_link.copy')} />
          </TransparentButton>
          <FullscreenPreview
            anchor={TransparentButton}
            label={label}
            previewModel={previewModel}
            attribute={value.attribute}
          >
            <Fullscreen
              title={__('pim_asset_manager.asset.button.fullscreen')}
              color={akeneoTheme.color.grey100}
              size={20}
            />
          </FullscreenPreview>
        </StyledButtonContainer>
      )}
    </Container>
  );
};

export const view = View;
