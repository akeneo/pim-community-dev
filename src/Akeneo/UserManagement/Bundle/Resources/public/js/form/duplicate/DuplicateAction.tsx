import React from 'react';
import {/*useRouter,*/ useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {useToggleState} from '@akeneo-pim-community/shared';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

type DuplicateActionProps = {
  userId: number;
};

const DuplicateAction = ({userId}: DuplicateActionProps) => {
  const translate = useTranslate();
  // const router = useRouter();
  const [isModalOpen, openModal, closeModal] = useToggleState(false);

  // const handleDeleted = () => {
  //   router.redirect(router.generate('pim_enrich_attribute_index'));
  //   closeModal();
  // };

  return (
    <>
      <button className="AknDropdown-menuLink duplicate" onClick={openModal}>
        {translate('pim_common.duplicate')}
      </button>
      {isModalOpen && <DuplicateUserApp userId={userId} onCancel={closeModal} />}
    </>
  );
};

export {DuplicateAction};
