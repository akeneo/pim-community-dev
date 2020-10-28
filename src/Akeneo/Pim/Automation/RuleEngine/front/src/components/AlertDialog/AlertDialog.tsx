import { Dialog, DialogStateReturn } from 'reakit/Dialog';
import React from 'react';
import { useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  dialog: DialogStateReturn;
  onValidate?: () => void;
  onCancel?: () => void;
  label: string;
  description: string;
  cancelLabel?: string;
  confirmLabel?: string;
  illustrationClassName?: string;
  validateButtonClassName?: string;
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
  validateButtonClassName = 'AknButton--important',
}) => {
  const translate = useTranslate();

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
              title={cancelLabel ?? translate('pim_common.cancel')}
              className='AknButton AknButton--grey AknButtonList-item'
              onClick={handleCancel}>
              {cancelLabel ?? translate('pim_common.cancel')}
            </button>
            <button
              title={confirmLabel ?? translate('pim_common.confirm')}
              className={`AknButton AknButtonList-item ${validateButtonClassName} ok`}
              onClick={handleConfirm}>
              {confirmLabel ?? translate('pim_common.confirm')}
            </button>
          </div>
        </div>
      </div>
    </Dialog>
  );
};

export { AlertDialog };
