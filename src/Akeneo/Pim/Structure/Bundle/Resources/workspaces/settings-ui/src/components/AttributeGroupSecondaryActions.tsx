import React from 'react';
import styled from 'styled-components';
import {useBooleanState, Dropdown} from 'akeneo-design-system';
import {SecondaryActions, useTranslate} from '@akeneo-pim-community/shared';
import {LOCKED_ATTRIBUTE_GROUP_CODE} from '../models';
import {DeleteAttributeGroupModal} from './DeleteAttributeGroupModal';

const Content = styled.div`
  margin-right: 10px;
`;

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
    <Content>
      <SecondaryActions>
        <Dropdown.Item onClick={openDeleteModal}>{translate('pim_common.delete')}</Dropdown.Item>
      </SecondaryActions>
      <DeleteAttributeGroupModal
        attributeGroupCode={attributeGroupCode}
        isOpen={isDeleteModalOpen}
        onClose={closeDeleteModal}
      />
    </Content>
  );
};

export {AttributeGroupSecondaryActions};
