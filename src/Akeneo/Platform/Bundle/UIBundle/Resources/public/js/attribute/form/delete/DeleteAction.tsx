import {NotificationLevel, useTranslate, useNotify, useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useToggleState} from '@akeneo-pim-community/shared';
import {DeleteModal} from "./DeleteModal";
import React from "react";

const AttributeRemover = require('pimui/js/remover/attribute');

type DeleteActionProps = {
  attributeCode: string;
}

const DeleteAction = ({attributeCode}: DeleteActionProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const notify = useNotify();
  const [isModalOpen, openModal, closeModal] = useToggleState(false);

  const handleConfirm = () => {
    AttributeRemover
      .remove(attributeCode)
      .done(() => {
        notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.attribute.flash.delete.success'));
        router.redirect(router.generate('pim_enrich_attribute_index'));
        closeModal();
      })
      .fail((xhr: any) => {
        let message = xhr.responseJSON && xhr.responseJSON.message
          ? xhr.responseJSON.message
          : translate('pim_enrich.entity.attribute.flash.delete.fail');

        notify(NotificationLevel.ERROR, message);
      });
  };

  return (
    <>
      <button className="AknDropdown-menuLink delete" onClick={openModal}>
        {translate('pim_common.delete')}
      </button>
      {isModalOpen &&
        <DeleteModal
            isOpen={isModalOpen}
            onClose={closeModal}
            onConfirm={handleConfirm}
        />
      }
    </>
  )
}

export {DeleteAction};
