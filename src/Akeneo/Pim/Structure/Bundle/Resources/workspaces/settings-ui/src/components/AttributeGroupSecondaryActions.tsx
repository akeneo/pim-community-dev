import React from 'react';
import {useBooleanState, Dropdown} from 'akeneo-design-system';
import {SecondaryActions, useTranslate} from '@akeneo-pim-community/shared';
import {LOCKED_ATTRIBUTE_GROUP_CODE} from '../models';
import {DeleteAttributeGroupModal} from './DeleteAttributeGroupModal';

type AttributeGroupSecondaryActionsProps = {
  attributeGroupCode: string;
};

const AttributeGroupSecondaryActions = ({attributeGroupCode}: AttributeGroupSecondaryActionsProps) => {
  const translate = useTranslate();
  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);

  const canDeleteAttributeGroup = LOCKED_ATTRIBUTE_GROUP_CODE !== attributeGroupCode;

  if (!canDeleteAttributeGroup) {
    return null;
  }

  return (
    <>
      <SecondaryActions>
        <Dropdown.Item onClick={openDeleteModal}>{translate('pim_common.delete')}</Dropdown.Item>
      </SecondaryActions>
      <DeleteAttributeGroupModal
        attributeGroupCode={attributeGroupCode}
        isOpen={isDeleteModalOpen}
        onClose={closeDeleteModal}
      />
    </>
  );
};

export {AttributeGroupSecondaryActions};
