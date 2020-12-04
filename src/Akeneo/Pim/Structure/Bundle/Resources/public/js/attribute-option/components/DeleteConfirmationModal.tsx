import React from 'react';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

interface deleteConfirmationModalProps {
  attributeOptionCode: string;
  confirmDelete: () => void;
  cancelDelete: () => void;
}

const DeleteConfirmationModal = ({attributeOptionCode, confirmDelete, cancelDelete}: deleteConfirmationModalProps) => {
  const translate = useTranslate();

  return (
    <div className="modal modal--fullPage in" role="attribute-option-delete-confirmation-modal">
      <div className="AknFullPage">
        <div className="AknFullPage-content AknFullPage-content--withIllustration">
          <div>
            <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete" />
          </div>
          <div>
            <div className="AknFullPage-titleContainer">
              <div className="AknFullPage-subTitle">{translate('pim_enrich.entity.attribute.plural_label')}</div>
              <div className="AknFullPage-title">
                {translate('pim_enrich.entity.attribute_option.module.edit.delete_confirmation_title_msg', {
                  optionCode: attributeOptionCode,
                })}
              </div>
              <div className="AknFullPage-description">
                {translate('pim_enrich.entity.attribute_option.module.edit.delete_confirmation_subtitle_msg')}
              </div>
            </div>
            <div className="modal-body" />
            <div className="AknButtonList">
              <div
                title="Delete"
                className="AknButton AknButtonList-item AknButton--apply AknButton--important ok"
                onClick={confirmDelete}
                role="attribute-option-confirm-delete-button"
              >
                {translate('pim_common.delete')}
              </div>
              <div
                className="AknButton AknButton--grey AknButtonList-item cancel"
                onClick={cancelDelete}
                role="attribute-option-confirm-cancel-button"
              >
                {translate('pim_common.cancel')}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div className="AknFullPage-cancel cancel" onClick={cancelDelete} role="attribute-option-confirm-cancel-button" />
    </div>
  );
};

export default DeleteConfirmationModal;
