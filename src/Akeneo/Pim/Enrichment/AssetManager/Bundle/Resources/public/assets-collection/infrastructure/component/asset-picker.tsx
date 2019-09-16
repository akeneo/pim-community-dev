import * as React from 'react';
import styled from 'styled-components';
import {Button} from 'akeneopimenrichmentassetmanager/platform/component/common/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import __ from 'akeneoreferenceentity/tools/translator';

type AssetPickerProps = {
  show: boolean;
  onClose: () => void;
};

const Modal = styled.div`
  border-radius: 0;
  border: none;
  top: 0;
  left: 0;
  position: fixed;
  z-index: 1050;
  background: white;
  width: 100%;
  height: 100%;
`;

const ConfirmButton = styled(Button)`
  width: 120px;
  height: 32px;
  text-align: center;
  position: absolute;
  top: 40px;
  right: 40px;
  line-height: 30px;
  font-size: 15px;
`;

const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: 36px;
  height: 44px;
  text-align: center;
  width: 100%;
  margin: 40px auto;
`;

export const AssetPicker = ({show, onClose}: AssetPickerProps) => {
  if (false === show) {
    return null;
  }

  return (
    <React.Fragment>
      <Modal>
        <Title>{__('pim_asset_manager.asset_picker.title')}</Title>
        <ConfirmButton buttonSize='medium' color='green' onClick={onClose}>{__('pim_asset_manager.asset_picker.confirm')}</ConfirmButton>
      </Modal>
    </React.Fragment>
  );
};
