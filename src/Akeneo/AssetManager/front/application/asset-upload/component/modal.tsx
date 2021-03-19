import React, {useReducer} from 'react';
import styled from 'styled-components';
import {ConfirmButton, Modal} from 'akeneoassetmanager/application/component/app/modal';
import {CloseButton} from 'akeneoassetmanager/application/component/app/close-button';
import LineList from 'akeneoassetmanager/application/asset-upload/component/line-list';
import Line from 'akeneoassetmanager/application/asset-upload/model/line';
import {
  AssetFamily,
  getAssetFamilyLabel,
  getAttributeAsMainMedia,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {reducer, State} from 'akeneoassetmanager/application/asset-upload/reducer/reducer';
import {
  editLineAction,
  removeAllLinesAction,
  removeLineAction,
} from 'akeneoassetmanager/application/asset-upload/reducer/action';
import FileDropZone from 'akeneoassetmanager/application/asset-upload/component/file-drop-zone';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {Reducer} from 'redux';
import {onFileDrop, retryFileUpload} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/upload';
import {onCreateAllAsset} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-create-all-assets';
import {hasAnUnsavedLine, getCreatedAssetCodes} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import limitFileUpload from 'akeneoassetmanager/application/asset-upload/utils/upload-limit';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {usePreventClosing} from 'akeneoassetmanager/application/hooks/prevent-closing';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {getColor, getFontSize, Key, useShortcut} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useUploader} from '@akeneo-pim-community/shared';

const Header = styled.div`
  background: ${getColor('white')};
  position: sticky;
  top: 0px;
  z-index: 2;

  :before {
    content: '';
    background: ${getColor('white')};
    display: block;
    position: absolute;
    height: 40px;
    top: -40px;
    width: 100%;
  }
`;

const Subtitle = styled.div`
  color: ${getColor('brand', 100)};
  margin-bottom: 12px;
  text-align: center;
  text-transform: uppercase;
  width: 100%;
`;

const Title = styled.div`
  color: ${getColor('grey', 140)};
  font-size: ${getFontSize('title')};
  line-height: ${getFontSize('title')};
  margin-bottom: 33px;
  text-align: center;
  width: 100%;
`;

type UploadModalHeaderProps = {
  label: string;
  confirmLabel: string;
  onClose: () => void;
  onConfirm: () => void;
};

const UploadModalHeader = React.memo(({label, confirmLabel, onClose, onConfirm}: UploadModalHeaderProps) => {
  const translate = useTranslate();

  return (
    <Header>
      <CloseButton title={translate('pim_asset_manager.close')} onClick={onClose} />
      <Subtitle>{label}</Subtitle>
      <Title>{translate('pim_asset_manager.asset.upload.title')}</Title>
      <ConfirmButton title={confirmLabel} color="green" onClick={onConfirm}>
        {confirmLabel}
      </ConfirmButton>
    </Header>
  );
});

type UploadModalProps = {
  assetFamily: AssetFamily;
  confirmLabel: string;
  locale: LocaleCode;
  channels: Channel[];
  locales: Locale[];
  onCancel: () => void;
  onAssetCreated: (assetCodes: AssetCode[]) => void;
};

const UploadModal = ({
  assetFamily,
  confirmLabel,
  locale,
  channels,
  locales,
  onCancel,
  onAssetCreated,
}: UploadModalProps) => {
  const translate = useTranslate();
  const [uploader] = useUploader('akeneo_asset_manager_file_upload');
  const [state, dispatch] = useReducer<Reducer<State>>(reducer, {lines: []});
  const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily) as NormalizedAttribute;
  const valuePerLocale = attributeAsMainMedia.value_per_locale;
  const valuePerChannel = attributeAsMainMedia.value_per_channel;

  // Close automatically the modal if there is lines and they are all created
  // This is a workaround because but we haven't found a proper way to do it directly after a successful onCreateAllAsset
  React.useEffect(() => {
    if (state.lines.length > 0 && !hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel)) {
      onAssetCreated(getCreatedAssetCodes(state.lines));
    }
  }, [state.lines, valuePerLocale, valuePerChannel, onCancel]);

  const isDirty = React.useCallback(() => {
    return hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel);
  }, [state.lines, valuePerLocale, valuePerChannel]);

  const handleClose = React.useCallback(() => {
    if (!isDirty() || confirm(translate('pim_asset_manager.asset.upload.discard_changes'))) {
      onCancel();
    }
  }, [isDirty, onCancel]);

  const handleConfirm = React.useCallback(() => {
    onCreateAllAsset(assetFamily, state.lines, dispatch);
  }, [assetFamily, state.lines, dispatch]);

  const handleDrop = React.useCallback(
    (event: React.ChangeEvent<HTMLInputElement>) => {
      event.preventDefault();
      event.stopPropagation();

      let files = event.target.files ? Object.values(event.target.files) : [];
      files = limitFileUpload(files, state.lines.length);

      onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    },
    [assetFamily, channels, locales, dispatch, state.lines.length, uploader]
  );

  const handleLineChange = React.useCallback((line: Line) => dispatch(editLineAction(line)), [dispatch]);

  const handleLineUploadRetry = React.useCallback((line: Line) => retryFileUpload(uploader, line, dispatch), [
    dispatch,
  ]);

  const handleLineRemove = React.useCallback((line: Line) => dispatch(removeLineAction(line)), [dispatch]);

  const handleLineRemoveAll = React.useCallback(() => dispatch(removeAllLinesAction()), [dispatch]);

  const label = React.useMemo(() => {
    return getAssetFamilyLabel(assetFamily, locale, true);
  }, [assetFamily, locale]);

  useShortcut(Key.Escape, handleClose);

  usePreventClosing(isDirty, translate('pim_asset_manager.asset.upload.discard_changes'));

  return (
    <Modal>
      <UploadModalHeader label={label} confirmLabel={confirmLabel} onClose={handleClose} onConfirm={handleConfirm} />
      <FileDropZone onDrop={handleDrop} />
      <LineList
        lines={state.lines}
        locale={locale}
        channels={channels}
        locales={locales}
        onLineChange={handleLineChange}
        onLineRemove={handleLineRemove}
        onLineUploadRetry={handleLineUploadRetry}
        onLineRemoveAll={handleLineRemoveAll}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    </Modal>
  );
};

export default UploadModal;
