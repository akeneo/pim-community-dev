import React, {useRef, useState} from 'react';
import {AttributeGroup} from '../../../models';
import {Button, useBooleanState, useAutoFocus, Helper} from 'akeneo-design-system';
import {DoubleCheckDeleteModal, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

type MassDeleteAttributeGroupsModalProps = {
  attributeGroups: AttributeGroup[];
  onConfirm: () => void;
};

const ModalContent = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const MassDeleteAttributeGroupsModal = ({attributeGroups, onConfirm}: MassDeleteAttributeGroupsModalProps) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [numberOfAttribute] = useState<number>(0);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const handleCancel = () => {
    closeMassDeleteModal();
  };

  return (
    <>
      <Button level="danger" onClick={() => openMassDeleteModal()}>
        {translate('pim_enrich.entity.attribute_group.mass_delete.button')}
      </Button>
      {isMassDeleteModalOpen && null !== attributeGroups && (
        <DoubleCheckDeleteModal
          title={translate('pim_enrich.entity.attribute_group.mass_delete.title')}
          doubleCheckInputLabel={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase', {
            confirmation_word: translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word'),
          })}
          textToCheck={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word')}
          onConfirm={() => onConfirm()}
          onCancel={handleCancel}
        >
          <ModalContent>
            <p>
              {translate('pim_enrich.entity.attribute_group.mass_delete.confirm', {
                count: String(attributeGroups.length),
              })}
            </p>
            {numberOfAttribute > 0 && (
              <Helper level={'error'}>
                {translate('pim_enrich.entity.attribute_group.mass_delete.attribute_warning', {
                  number_of_attribute: numberOfAttribute,
                })}
              </Helper>
            )}
          </ModalContent>
        </DoubleCheckDeleteModal>
      )}
    </>
  );
};

export {MassDeleteAttributeGroupsModal};
