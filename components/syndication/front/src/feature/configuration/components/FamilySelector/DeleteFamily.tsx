import {DeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import {IconButton, DeleteIcon, useBooleanState} from 'akeneo-design-system';
import React from 'react';

type DeleteFamilyProps = {
  family: {label: string; code: string};
  onFamilyDelete: (familyToDelete: string) => void;
};

const DeleteFamily = ({family, onFamilyDelete}: DeleteFamilyProps) => {
  const translate = useTranslate();
  const [isOpen, open, close] = useBooleanState(false);

  return (
    <>
      <IconButton
        ghost
        icon={<DeleteIcon />}
        level="danger"
        onClick={event => {
          event.preventDefault();
          event.stopPropagation();
          open();
        }}
        size="small"
        title={translate('akeneo.syndication.platform.family.delete.title')}
      />
      {isOpen && (
        <DeleteModal
          title={translate('akeneo.syndication.platform.family.delete.title')}
          confirmButtonLabel={translate('pim_common.confirm')}
          onConfirm={() => onFamilyDelete(family.code)}
          onCancel={close}
        >
          {translate('akeneo.syndication.platform.family.delete.confirm', {family: family.label})}
        </DeleteModal>
      )}
    </>
  );
};

export {DeleteFamily};
