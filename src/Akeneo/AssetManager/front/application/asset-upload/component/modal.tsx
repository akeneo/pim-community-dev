import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import styled from 'styled-components';
import {ConfirmButton, Header, Modal} from 'akeneoassetmanager/application/component/app/modal';
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
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {Reducer} from 'redux';
import {onFileDrop} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-file-drop';
import {onCreateAllAsset} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-create-all-assets';
import {hasAnUnsavedLine} from 'akeneoassetmanager/application/asset-upload/utils/utils';
import Locale, {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import Channel from 'akeneoassetmanager/domain/model/channel';
import {useShortcut} from 'akeneoassetmanager/application/hooks/input';
import Key from 'akeneoassetmanager/tools/key';

const Subtitle = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  margin-bottom: 12px;
  text-align: center;
  text-transform: uppercase;
  width: 100%;
`;

const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey140};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  margin-bottom: 33px;
  text-align: center;
  width: 100%;
`;

type UploadModalProps = {
  assetFamily: AssetFamily;
  locale: LocaleCode;
  channels: Channel[];
  locales: Locale[];
  // @TODO merge this two callbacks into one onClose()
  onCancel: () => void;
  onAssetCreated: () => void;
};

const UploadModal = ({assetFamily, locale, channels, locales, onCancel, onAssetCreated}: UploadModalProps) => {
  const [state, dispatch] = React.useReducer<Reducer<State>>(reducer, {lines: []});
  const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily) as NormalizedAttribute;
  const valuePerLocale = attributeAsMainMedia.value_per_locale;
  const valuePerChannel = attributeAsMainMedia.value_per_channel;

  // Close automatically the modal if there is lines and they are all created
  // This is a workaround because but we haven't found a proper way to do it directly after a successful onCreateAllAsset
  React.useEffect(() => {
    if (state.lines.length > 0 && !hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel)) {
      onAssetCreated();
    }
  }, [state.lines, valuePerLocale, valuePerChannel, onCancel]);

  const handleClose = React.useCallback(() => {
    const isDirty = hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel);

    if (!isDirty || confirm(__('pim_asset_manager.asset.upload.discard_changes'))) {
      onCancel();
    }
  }, [state.lines, valuePerLocale, valuePerChannel, onCancel]);

  const handleConfirm = React.useCallback(() => {
    onCreateAllAsset(assetFamily, state.lines, dispatch);
  }, [assetFamily, state.lines, dispatch]);

  const handleDrop = React.useCallback(
    (event: React.ChangeEvent<HTMLInputElement>) => {
      event.preventDefault();
      event.stopPropagation();

      const files = event.target.files ? Object.values(event.target.files) : [];
      onFileDrop(files, assetFamily, channels, locales, dispatch);
    },
    [assetFamily, dispatch]
  );

  useShortcut(Key.Escape, handleClose);

  return (
    <Modal>
      <Header>
        <CloseButton title={__('pim_asset_manager.close')} onClick={handleClose} />
        <Subtitle>{getAssetFamilyLabel(assetFamily, locale, true)}</Subtitle>
        <Title>{__('pim_asset_manager.asset.upload.title')}</Title>
        <ConfirmButton title={__('pim_asset_manager.asset.upload.confirm')} color="green" onClick={handleConfirm}>
          {__('pim_asset_manager.asset.upload.confirm')}
        </ConfirmButton>
      </Header>
      <FileDropZone onDrop={handleDrop} />
      <LineList
        lines={state.lines}
        locale={locale}
        channels={channels}
        locales={locales}
        onLineChange={(line: Line) => {
          dispatch(editLineAction(line));
        }}
        onLineRemove={(line: Line) => {
          dispatch(removeLineAction(line));
        }}
        onLineRemoveAll={() => {
          dispatch(removeAllLinesAction());
        }}
        valuePerLocale={valuePerLocale}
        valuePerChannel={valuePerChannel}
      />
    </Modal>
  );
};

export default UploadModal;
