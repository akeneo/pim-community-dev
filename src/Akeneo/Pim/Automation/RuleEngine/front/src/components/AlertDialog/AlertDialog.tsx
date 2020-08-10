import { Dialog, DialogStateReturn } from 'reakit/Dialog';
import React from 'react';

type Props = {
  dialog: DialogStateReturn;
  onValidate?: () => void;
  onCancel?: () => void;
  label: string;
  description: string;
  cancelLabel: string;
  confirmLabel: string;
  illustrationClassName?: string;
};

const AlertDialog: React.FC<Props> = ({
  dialog,
  onValidate,
  onCancel,
  label,
  description,
  cancelLabel,
  confirmLabel,
  illustrationClassName = 'AknFullPage-illustration--delete',
}) => {
  const handleConfirm = () => {
    dialog.hide();
    if (onValidate) {
      onValidate();
    }
  };

  const handleCancel = () => {
    dialog.hide();
    if (onCancel) {
      onCancel();
    }
  };

  return (
    <Dialog
      {...dialog}
      role='alertdialog'
      aria-label={label}
      aria-describedby='dialog_desc'
      className='AknFullPage'>
      <div className='AknFullPage-content AknFullPage-content--withIllustration'>
        <div>
          <div
            className={`AknFullPage-image AknFullPage-illustration ${illustrationClassName}`}
          />
        </div>
        <div>
          <div className='AknFullPage-titleContainer'>
            <div className='AknFullPage-title'>{label}</div>
            <div id='dialog_desc' className='AknFullPage-description'>
              {description}
            </div>
          </div>
          <div className='AknButtonList'>
            <button
              title={cancelLabel}
              className='AknButton AknButton--grey AknButtonList-item'
              onClick={handleCancel}>
              {cancelLabel}
            </button>
            <button
              title={confirmLabel}
              className='AknButton AknButtonList-item AknButton--apply AknButton--important ok'
              onClick={handleConfirm}>
              {confirmLabel}
            </button>
          </div>
        </div>
      </div>
    </Dialog>
  );
};

export { AlertDialog };
