import React, {useRef, useState} from 'react';
import styled from 'styled-components';
import {Button, useBooleanState, useAutoFocus, Helper, SelectInput, Field} from 'akeneo-design-system';
import {DoubleCheckDeleteModal, getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../../models';

const ModalContent = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

type MassDeleteAttributeGroupsModalProps = {
  selectedCount: number;
  childrenAttributesCount: number;
  targetAttributeGroups: AttributeGroup[];
};

const MassDeleteAttributeGroupsModal = ({
  selectedCount,
  childrenAttributesCount,
  targetAttributeGroups,
}: MassDeleteAttributeGroupsModalProps) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [replacementAttributeGroup, setReplacementAttributeGroup] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const catalogLocale = useUserContext().get('catalogLocale');

  const handleConfirm = () => {
    closeMassDeleteModal();
    // TODO launch job, will be implemented in RAB-1278
  };

  useAutoFocus(inputRef);

  return (
    <>
      <Button level="danger" onClick={openMassDeleteModal}>
        {translate('pim_enrich.entity.attribute_group.mass_delete.button')}
      </Button>
      {isMassDeleteModalOpen && (
        <DoubleCheckDeleteModal
          title={translate('pim_enrich.entity.attribute_group.mass_delete.title')}
          doubleCheckInputLabel={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase', {
            confirmation_word: translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word'),
          })}
          textToCheck={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word')}
          onConfirm={handleConfirm}
          onCancel={closeMassDeleteModal}
        >
          <ModalContent>
            <p>
              {translate(
                'pim_enrich.entity.attribute_group.mass_delete.confirm',
                {
                  count: selectedCount,
                },
                selectedCount
              )}
            </p>
            {0 < childrenAttributesCount && (
              <>
                <Helper level="error">
                  {translate('pim_enrich.entity.attribute_group.mass_delete.attribute_warning', {
                    number_of_attribute: childrenAttributesCount,
                    childrenAttributesCount,
                  })}
                </Helper>
                <Field
                  label={translate('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group', {
                    number_of_attribute: childrenAttributesCount,
                    childrenAttributesCount,
                  })}
                >
                  <SelectInput
                    emptyResultLabel={translate('pim_enrich.entity.attribute_group.mass_delete.empty_result_label')}
                    onChange={(value: string | null) => setReplacementAttributeGroup(value)}
                    placeholder={translate('pim_enrich.entity.attribute_group.mass_delete.placeholder')}
                    value={replacementAttributeGroup}
                    openLabel={translate('pim_enrich.entity.attribute_group.mass_delete.open_label')}
                  >
                    {targetAttributeGroups.map(attributeGroup => (
                      <SelectInput.Option key={attributeGroup.code} value={attributeGroup.code}>
                        {getLabel(attributeGroup.labels, catalogLocale, attributeGroup.code)}
                      </SelectInput.Option>
                    ))}
                  </SelectInput>
                </Field>
              </>
            )}
          </ModalContent>
        </DoubleCheckDeleteModal>
      )}
    </>
  );
};

export {MassDeleteAttributeGroupsModal};
