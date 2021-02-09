import React from 'react';
import {useTranslate, useRouter} from '@akeneo-pim-community/legacy-bridge';
import {useToggleState} from '@akeneo-pim-community/shared';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

type DuplicateActionProps = {
  userId: number;
};

const DuplicateOption = ({userId}: DuplicateActionProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const [isModalOpen, openModal, closeModal] = useToggleState(false);

  const onDuplicateSuccess = (duplicatedUserId: string) => {
    router.redirect(router.generate('pim_user_edit', {identifier: duplicatedUserId}));
  };

  return (
    <>
      <button className="AknDropdown-menuLink duplicate" onClick={openModal}>
        {translate('pim_common.duplicate')}
      </button>
      {isModalOpen && (
        <DuplicateUserApp userId={userId} onCancel={closeModal} onDuplicateSuccess={onDuplicateSuccess} />
      )}
    </>
  );
};

export {DuplicateOption};
