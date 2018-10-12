import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';

const DeleteModal = ({
  message,
  title,
  onConfirm,
  onCancel,
}: {
  message: string;
  title: string;
  onConfirm: () => void;
  onCancel: () => void;
}) => {
  return (
    <div className="modal modal--fullPage in" aria-hidden="false" style={{zIndex: 1041}}>
      <div className="AknFullPage AknFullPage--modal ">
        <div className="AknFullPage-content AknFormContainer--withPadding AknFormContainer--centered AknFormContainer--expanded">
          <div className="AknFullPage-left">
            <div className="AknFullPage-image AknFullPage-illustration AknFullPage-illustration--delete" />
          </div>
          <div className="AknFullPage-right">
            <div className="AknFullPage-subTitle">{title}</div>
            <div className="AknFullPage-title">{__('pim_reference_entity.record.delete.subtitle')}</div>
            <div className="AknFullPage-description AknFullPage-description--bottom">{message}</div>
            <div className="AknButtonList">
              <button className="AknButtonList-item AknButton AknButton--grey cancel" onClick={onCancel}>
                {__('pim_reference_entity.record.delete.button.cancel')}
              </button>

              <button className="AknButtonList-item AknButton AknButton--important ok" onClick={onConfirm}>
                {__('pim_reference_entity.record.delete.button.confirm')}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default DeleteModal;
