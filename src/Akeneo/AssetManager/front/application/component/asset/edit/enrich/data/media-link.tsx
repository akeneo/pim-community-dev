import React from 'react';
import {connect} from 'react-redux';
import styled from 'styled-components';
import {FullscreenIcon, Key} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getMediaPreviewUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {
  isMediaLinkData,
  mediaLinkDataFromString,
  mediaLinkDataStringValue,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {getMediaData} from 'akeneoassetmanager/domain/model/asset/data';
import {MediaPreviewType} from 'akeneoassetmanager/domain/model/asset/media-preview';
import {setValueData, isValueEmpty} from 'akeneoassetmanager/domain/model/asset/value';
import {FullscreenPreview} from 'akeneoassetmanager/application/component/asset/edit/preview/fullscreen-preview';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import {
  Action,
  DownloadAction,
  CopyUrlAction,
  Container,
  Thumbnail,
  Actions,
  ThumbnailPlaceholder,
  ReloadAction,
} from 'akeneoassetmanager/application/component/asset/edit/enrich/data/media';
import {useRegenerate} from 'akeneoassetmanager/application/hooks/regenerate';
import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {doReloadAllPreviews} from 'akeneoassetmanager/application/action/asset/reloadPreview';
import {ViewGenerator, ViewGeneratorProps} from 'akeneoassetmanager/application/configuration/value';

const MediaLinkInput = styled.input`
  ::placeholder {
    color: ${props => props.theme.color.grey120};
  }

  :disabled,
  :read-only {
    cursor: not-allowed;
    border: none;
    color: ${props => props.theme.color.grey100};

    ::placeholder {
      color: ${props => props.theme.color.grey100};
    }
  }
`;

const ThumbnailContainer = styled.div`
  position: relative;
  display: flex;
  margin-right: 15px;

  ${Thumbnail} {
    margin: 0;
  }
`;

const View = ({
  value,
  locale,
  onChange,
  onSubmit,
  canEditData,
  reloadPreview,
  onReloadPreview,
}: ViewGeneratorProps & {
  reloadPreview: boolean;
  onReloadPreview: () => void;
}) => {
  const translate = useTranslate();

  if (!isMediaLinkData(value.data) || !isMediaLinkAttribute(value.attribute)) {
    return null;
  }

  const mediaPreviewUrl = getMediaPreviewUrl({
    type: MediaPreviewType.ThumbnailSmall,
    attributeIdentifier: value.attribute.identifier,
    data: getMediaData(value.data),
  });

  React.useEffect(() => {
    if (reloadPreview) {
      doRegenerate();
    }
  }, [reloadPreview]);
  const [regenerate, doRegenerate, refreshedUrl] = useRegenerate(mediaPreviewUrl);

  const label = getLabelInCollection(
    value.attribute.labels,
    localeReferenceStringValue(locale),
    true,
    value.attribute.code
  );

  return (
    <Container>
      {regenerate ? (
        <ThumbnailPlaceholder className="AknLoadingPlaceHolder" />
      ) : (
        <ThumbnailContainer>
          <Thumbnail src={refreshedUrl} alt={translate('pim_asset_manager.attribute.media_type_preview')} />
        </ThumbnailContainer>
      )}
      <MediaLinkInput
        id={`pim_asset_manager.asset.enrich.${value.attribute.code}`}
        autoComplete="off"
        className={`AknTextField AknTextField--light ${
          value.attribute.value_per_locale ? 'AknTextField--localizable' : ''
        }`}
        value={mediaLinkDataStringValue(value.data)}
        onChange={e => onChange(setValueData(value, mediaLinkDataFromString(e.currentTarget.value)))}
        onKeyDown={e => Key.Enter === e.key && onSubmit?.()}
        disabled={!canEditData}
        readOnly={!canEditData}
        placeholder={translate(`pim_asset_manager.attribute.media_link.${canEditData ? 'placeholder' : 'read_only'}`)}
      />
      {!isValueEmpty(value) && (
        <Actions>
          <ReloadAction size={20} onReload={onReloadPreview} data={value.data} attribute={value.attribute} />
          <CopyUrlAction size={20} data={value.data} attribute={value.attribute} />
          <DownloadAction size={20} data={value.data} attribute={value.attribute} />
          <FullscreenPreview anchor={Action} label={label} data={value.data} attribute={value.attribute}>
            <FullscreenIcon title={translate('pim_asset_manager.asset.button.fullscreen')} size={20} />
          </FullscreenPreview>
        </Actions>
      )}
    </Container>
  );
};

export const view = connect(
  (state: EditState) => ({
    reloadPreview: state.reloadPreview,
  }),
  dispatch => ({
    onReloadPreview: () => dispatch(doReloadAllPreviews() as any),
  })
)(View) as ViewGenerator;
