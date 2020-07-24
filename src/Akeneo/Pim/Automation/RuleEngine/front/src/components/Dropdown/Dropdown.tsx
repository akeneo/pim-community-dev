import React from 'react';
import { AlertDialog } from '../AlertDialog/AlertDialog';
import { useDialogState, DialogDisclosure } from 'reakit/Dialog';
import { useTranslate } from '../../dependenciesTools/hooks';

type Props = {
  handleDeleteRule: () => Promise<any>;
};

const Dropdown: React.FC<Props> = ({ handleDeleteRule }) => {
  const dialog = useDialogState();
  const translate = useTranslate();

  return (
    <div className='AknSecondaryActions AknDropdown AknButtonList-item'>
      <div className='AknSecondaryActions-button' data-toggle='dropdown'></div>
      <div className='AknDropdown-menu AknDropdown-menu--right'>
        <div className='AknDropdown-menuTitle'>
          {translate('pimee_catalog_rule.dropdown.title')}
        </div>
        <DialogDisclosure {...dialog} className='AknDropdown-menuLink'>
          {translate('pimee_catalog_rule.form.delete.label')}
        </DialogDisclosure>
        <AlertDialog
          dialog={dialog}
          onValidate={handleDeleteRule}
          cancelLabel={translate('pim_common.cancel')}
          confirmLabel={translate('pim_common.confirm')}
          label={translate('pimee_catalog_rule.form.edit.actions.delete.label')}
          description={translate('pimee_catalog_rule.form.delete.description')}
        />
      </div>
    </div>
  );
};

export { Dropdown };
