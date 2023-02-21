import React, {useEffect, useRef, useState} from 'react';
import {AttributeGroup} from '../../../models';
import {Button, useBooleanState, useAutoFocus, Helper, SelectInput, Field} from 'akeneo-design-system';
import {DoubleCheckDeleteModal, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {getLabel} from 'pimui/js/i18n';

type MassDeleteAttributeGroupsModalProps = {
  selectedAttributeGroups: AttributeGroup[];
  unselectAttributeGroups: AttributeGroup[];
  onConfirm: () => void;
};

const ModalContent = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

const MassDeleteAttributeGroupsModal = ({
  selectedAttributeGroups,
  unselectAttributeGroups,
  onConfirm,
}: MassDeleteAttributeGroupsModalProps) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [numberOfAttribute, setNumberOfAttribute] = useState<number>(0);
  const [replacementAttributeGroup, setReplacementAttributeGroup] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);
  const userContext = useUserContext();

  useAutoFocus(inputRef);

  useEffect(() => {
    if (null !== selectedAttributeGroups) {
      let numberOfAttribute = 0;
      selectedAttributeGroups.forEach(attributeGroup => {
        numberOfAttribute += attributeGroup.attribute_count;
      });
      setNumberOfAttribute(numberOfAttribute);
    }
  }, [selectedAttributeGroups]);

  return (
    <>
      <Button level="danger" onClick={openMassDeleteModal}>
        {translate('pim_enrich.entity.attribute_group.mass_delete.button')}
      </Button>
      {isMassDeleteModalOpen && null !== selectedAttributeGroups && (
        <DoubleCheckDeleteModal
          title={translate('pim_enrich.entity.attribute_group.mass_delete.title')}
          doubleCheckInputLabel={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase', {
            confirmation_word: translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word'),
          })}
          textToCheck={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word')}
          onConfirm={() => onConfirm()}
          onCancel={closeMassDeleteModal}
        >
          <ModalContent>
            <p>
              {translate(
                'pim_enrich.entity.attribute_group.mass_delete.confirm',
                {
                  count: selectedAttributeGroups.length,
                },
                selectedAttributeGroups.length
              )}
            </p>
            {numberOfAttribute > 0 && (
              <>
                <Helper level="error">
                  {translate('pim_enrich.entity.attribute_group.mass_delete.attribute_warning', {
                    number_of_attribute: numberOfAttribute,
                  })}
                </Helper>
                <Field
                  label={translate('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group', {
                    number_of_attribute: numberOfAttribute,
                  })}
                >
                  <SelectInput
                    emptyResultLabel={translate('pim_enrich.entity.attribute_group.mass_delete.empty_result_label')}
                    onChange={(value: string | null) => setReplacementAttributeGroup(value)}
                    placeholder={translate('pim_enrich.entity.attribute_group.mass_delete.placeholder')}
                    value={replacementAttributeGroup}
                    openLabel={translate('pim_enrich.entity.attribute_group.mass_delete.open_label')}
                  >
                    {unselectAttributeGroups.map(attributeGroup => (
                      <SelectInput.Option key={attributeGroup.code} value={attributeGroup.code}>
                        {getLabel(attributeGroup.labels, userContext.get('catalogLocale'), attributeGroup.code)}
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
