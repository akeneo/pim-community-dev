import React from 'react';
import {useBooleanState} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {DeleteModal} from './DeleteModal';

type DeleteActionProps = {
  attributeCode: string;
};

const DeleteAction = ({attributeCode}: DeleteActionProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const [isModalOpen, openModal, closeModal] = useBooleanState(false);

  const handleDeleted = () => {
    router.redirect(router.generate('pim_enrich_attribute_index'));
    closeModal();
  };

  return (
    <>
      <button className="AknDropdown-menuLink delete" onClick={openModal}>
        {translate('pim_common.delete')}
      </button>
      {isModalOpen && <DeleteModal onCancel={closeModal} onSuccess={handleDeleted} attributeCode={attributeCode} />}
    </>
  );
};

export {DeleteAction};
