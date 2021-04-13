import React, {useReducer, useCallback, useMemo, useEffect, ChangeEvent} from 'react';
import {Reducer} from 'redux';
import styled from 'styled-components';
import {Button, Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useUploader} from '@akeneo-pim-community/shared';
import {LineList} from 'akeneoassetmanager/application/asset-upload/component/line-list';
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
import {onFileDrop, retryFileUpload} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/upload';
import {onCreateAllAsset} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-create-all-assets';
import {hasAnUnsavedLine, getCreatedAssetCodes} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import limitFileUpload from 'akeneoassetmanager/application/asset-upload/utils/upload-limit';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {usePreventClosing} from 'akeneoassetmanager/application/hooks/prevent-closing';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

const Content = styled.div`
  flex: 1;
  display: flex;
  flex-direction: column;
  width: 100%;
  overflow-y: auto;
`;

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
  useEffect(() => {
    if (state.lines.length > 0 && !hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel)) {
      onAssetCreated(getCreatedAssetCodes(state.lines));
    }
  }, [state.lines, valuePerLocale, valuePerChannel, onCancel]);

  const isDirty = useCallback(() => {
    return hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel);
  }, [state.lines, valuePerLocale, valuePerChannel]);

  const handleClose = useCallback(() => {
    if (!isDirty() || confirm(translate('pim_asset_manager.asset.upload.discard_changes'))) {
      onCancel();
    }
  }, [isDirty, onCancel]);

  const handleConfirm = useCallback(() => {
    onCreateAllAsset(assetFamily, state.lines, dispatch);
  }, [assetFamily, state.lines, dispatch]);

  const handleDrop = useCallback(
    (event: ChangeEvent<HTMLInputElement>) => {
      event.preventDefault();
      event.stopPropagation();

      let files = event.target.files ? Object.values(event.target.files) : [];
      files = limitFileUpload(files, state.lines.length);

      onFileDrop(uploader, files, assetFamily, channels, locales, dispatch);
    },
    [assetFamily, channels, locales, dispatch, state.lines.length, uploader]
  );

  const handleLineChange = useCallback((line: Line) => dispatch(editLineAction(line)), [dispatch]);

  const handleLineUploadRetry = useCallback((line: Line) => retryFileUpload(uploader, line, dispatch), [dispatch]);

  const handleLineRemove = useCallback((line: Line) => dispatch(removeLineAction(line)), [dispatch]);

  const handleLineRemoveAll = useCallback(() => dispatch(removeAllLinesAction()), [dispatch]);

  const label = useMemo(() => getAssetFamilyLabel(assetFamily, locale, true), [assetFamily, locale]);

  usePreventClosing(isDirty, translate('pim_asset_manager.asset.upload.discard_changes'));

  return (
    <Modal onClose={handleClose} closeTitle={translate('pim_common.close')}>
      <Modal.SectionTitle color="brand">{label}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_asset_manager.asset.upload.title')}</Modal.Title>
      <Modal.TopRightButtons>
        <Button onClick={handleConfirm}>{confirmLabel}</Button>
      </Modal.TopRightButtons>
      <Content>
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
      </Content>
    </Modal>
  );
};

export {UploadModal};
