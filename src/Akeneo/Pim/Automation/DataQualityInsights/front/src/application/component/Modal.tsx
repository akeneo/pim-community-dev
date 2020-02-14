import React from "react";

const __ = require('oro/translator');

interface ModalProps {
  cssClass: string;
  title: string;
  subtitle: string;
  description: string;
  illustrationLink: string;
  modalContent: any;
  onConfirm: () => void;
  onDismissModal: () => void;
  enableSaveButton: boolean;
}

const Modal = ({cssClass, title, subtitle, description, illustrationLink, modalContent, onConfirm, onDismissModal, enableSaveButton}: ModalProps) => {

  const modalClass = `modal in ${cssClass}`;

  return (
    <div className={modalClass} data-testid={'dqiModal'}>
      <div className="AknFullPage">
        <div className="AknFullPage-content AknFullPage-content--withIllustration">
          <div>
            <img src={illustrationLink}/>
          </div>
          <div>
            <div className="AknFullPage-titleContainer">
              <div className="AknFullPage-subTitle">{title}</div>
              <div className="AknFullPage-title">{subtitle}</div>
              <div className="AknFullPage-description">{description}</div>
            </div>
            <div className="modal-body">

              {modalContent}

            </div>
          </div>
        </div>
      </div>

      <div className="AknFullPage-cancel cancel" onClick={() => onDismissModal()}/>
      <span className={`AknButton AknFullPage-ok AknButton--apply ${!enableSaveButton ? 'AknButton--disabled' : ''}`} onClick={() => enableSaveButton && onConfirm()} data-testid='dqiValidateModal'>
        {__('pim_common.save')}
      </span>

    </div>
  );
};

export default Modal;
