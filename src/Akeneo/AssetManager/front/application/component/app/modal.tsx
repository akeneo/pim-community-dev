import * as React from 'react';
import styled from 'styled-components';
import {Button, ButtonContainer} from 'akeneoassetmanager/application/component/app/button';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {AssetCategoriesIllustration, Key, useShortcut} from 'akeneo-design-system';

export const Modal = styled.div`
  display: flex;
  flex-direction: column;
  border-radius: 0;
  border: none;
  top: 0;
  left: 0;
  position: fixed;
  z-index: 1030;
  background: white;
  width: 100%;
  height: 100%;
  padding: 40px;
  overflow-x: auto;
`;

export const ScrollableModal = styled(Modal)`
  overflow: hidden;
  padding-bottom: 0;
`;

export const ConfirmButton = styled(Button)`
  position: absolute;
  top: 0;
  right: 0;
`;

export const Title = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.purple100};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  line-height: ${(props: ThemedProps<void>) => props.theme.fontSize.title};
  margin-bottom: 23px;
  text-align: center;
  width: 100%;
`;

export const SubTitle = styled.div`
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
  font-size: ${(props: ThemedProps<void>) => props.theme.fontSize.default};
  margin-bottom: 4px;
  text-align: center;
  text-transform: uppercase;
  width: 100%;
`;

export const Header = styled.div`
  position: relative;
`;

type ConfirmModalProps = {
  titleContent: string;
  content: string;
  cancelButtonText: string;
  confirmButtonText: string;
  onCancel: () => void;
  onConfirm: () => void;
};

export const ConfirmModal = ({
  titleContent,
  content,
  cancelButtonText,
  confirmButtonText,
  onCancel,
  onConfirm,
}: ConfirmModalProps) => {
  useShortcut(Key.Escape, onCancel);

  return (
    <Modal>
      <div className="AknFullPage modal-body">
        <div className="AknFullPage-content AknFullPage-content--withIllustration">
          <div>
            <AssetCategoriesIllustration />
          </div>
          <div>
            <div className="AknFullPage-titleContainer">
              <div className="AknFullPage-title">{titleContent}</div>
              <div className="AknFullPage-description">{content}</div>
            </div>
            <ButtonContainer>
              <Button color="grey" onClick={onCancel}>
                {cancelButtonText}
              </Button>
              <Button color="blue" onClick={onConfirm}>
                {confirmButtonText}
              </Button>
            </ButtonContainer>
          </div>
        </div>
      </div>
      <div className="AknFullPage-cancel cancel" onClick={onCancel} />
    </Modal>
  );
};
