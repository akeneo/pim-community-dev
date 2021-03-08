import React, {useRef, useEffect} from 'react';
import {Button} from 'akeneo-design-system';
import {ButtonContainer} from './button';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type DeleteModalProps = {
  message: string;
  title: string;
  onConfirm: () => void;
  onCancel: () => void;
};

//TODO Use DSM Modal
const DeleteModal = ({message, title, onConfirm, onCancel}: DeleteModalProps) => {
  const cancelButtonRef = useRef<HTMLButtonElement>(null);
  const translate = useTranslate();

  useEffect(() => {
    if (null !== cancelButtonRef.current) {
      cancelButtonRef.current.focus();
    }
  }, []);

  return (
    <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
      <div className="AknFullPage">
        <div className="AknFullPage-content AknFullPage-content--withIllustration">
          <div>
            <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete" />
          </div>
          <div>
            <div className="AknFullPage-titleContainer">
              <div className="AknFullPage-subTitle">{title}</div>
              <div className="AknFullPage-title">{translate('pim_asset_manager.modal.delete.subtitle')}</div>
              <div className="AknFullPage-description AknFullPage-description--bottom">{message}</div>
            </div>
            <ButtonContainer>
              <Button ref={cancelButtonRef} level="tertiary" onClick={onCancel}>
                {translate('pim_asset_manager.modal.delete.button.cancel')}
              </Button>
              <Button level="danger" onClick={onConfirm}>
                {translate('pim_asset_manager.modal.delete.button.confirm')}
              </Button>
            </ButtonContainer>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DeleteModal;
