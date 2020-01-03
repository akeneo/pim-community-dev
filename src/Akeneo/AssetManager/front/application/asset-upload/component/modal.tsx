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
import {LocaleCode} from 'akeneoassetmanager/domain/model/locale';
import {onFileDrop} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-file-drop';
import {onCreateAllAsset} from 'akeneoassetmanager/application/asset-upload/reducer/thunks/on-create-all-assets';
import {hasAnUnsavedLine} from 'akeneoassetmanager/application/asset-upload/utils/utils';

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
  onCancel: () => void;
  onAssetCreated: () => void;
};

const UploadModal = ({assetFamily, locale, onCancel}: UploadModalProps) => {
  const [state, dispatch] = React.useReducer<Reducer<State>>(reducer, {lines: []});
  const attributeAsMainMedia = getAttributeAsMainMedia(assetFamily) as NormalizedAttribute;
  const valuePerLocale = attributeAsMainMedia.value_per_locale;
  const valuePerChannel = attributeAsMainMedia.value_per_channel;

  const canClose = (): boolean => {
    const message = __('pim_asset_manager.asset.upload.discard_changes');
    return hasAnUnsavedLine(state.lines, valuePerLocale, valuePerChannel) ? confirm(message) : true;
  };

  const onClose = (): void => {
    if (canClose()) {
      onCancel();
    }
  };

  return (
    <Modal>
      <Header>
        <CloseButton title={__('pim_asset_manager.close')} onClick={onClose} />
        <Subtitle>{getAssetFamilyLabel(assetFamily, locale, true)}</Subtitle>
        <Title>{__('pim_asset_manager.asset.upload.title')}</Title>
        <ConfirmButton
          title={__('pim_asset_manager.asset.upload.confirm')}
          color="green"
          onClick={() => {
            onCreateAllAsset(assetFamily, state.lines, dispatch);
          }}
        >
          {__('pim_asset_manager.asset.upload.confirm')}
        </ConfirmButton>
      </Header>
      <FileDropZone
        onDrop={(event: React.ChangeEvent<HTMLInputElement>) => {
          event.preventDefault();
          event.stopPropagation();

          const files = event.target.files ? Object.values(event.target.files) : [];
          onFileDrop(files, assetFamily, dispatch);
        }}
      />
      <LineList
        lines={state.lines}
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
