import React, {useEffect, useRef, useState} from 'react';
import styled from 'styled-components';
import {Button, useBooleanState, useAutoFocus, Helper, SelectInput, Field} from 'akeneo-design-system';
import {
  DoubleCheckDeleteModal,
  NotificationLevel,
  getLabel,
  useTranslate,
  useNotify,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {AttributeGroup} from '../../../models';
import {useMassDeleteAttributeGroups} from '../../../hooks';

const ModalContent = styled.div`
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

type MassDeleteAttributeGroupsModalProps = {
  impactedAttributeGroups: AttributeGroup[];
  availableTargetAttributeGroups: AttributeGroup[];
};

const MassDeleteAttributeGroupsModal = ({
  impactedAttributeGroups,
  availableTargetAttributeGroups,
}: MassDeleteAttributeGroupsModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const userContext = useUserContext();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [replacementAttributeGroup, setReplacementAttributeGroup] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const catalogLocale = useUserContext().get('catalogLocale');

  const [isLoading, launchMassDeleteAttributeGroups] = useMassDeleteAttributeGroups();
  const impactedAttributesCount = impactedAttributeGroups.reduce(
    (totalCount, {attribute_count}) => totalCount + attribute_count,
    0
  );

  useAutoFocus(inputRef);

  const handleLaunchMassDelete = async () => {
    if (isLoading) return;

    try {
      await launchMassDeleteAttributeGroups(impactedAttributeGroups);
      notify(NotificationLevel.INFO, translate('pim_enrich.entity.attribute_group.flash.mass_delete.success'));
      closeMassDeleteModal();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute_group.flash.mass_delete.fail'));
    }
  };

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
          canConfirmDelete={!isLoading && (null !== replacementAttributeGroup || 0 === impactedAttributesCount)}
          onConfirm={handleLaunchMassDelete}
          onCancel={closeMassDeleteModal}
        >
          <ModalContent>
            <p>
              {translate(
                'pim_enrich.entity.attribute_group.mass_delete.confirm',
                {
                  count: impactedAttributeGroups.length,
                },
                impactedAttributeGroups.length
              )}
            </p>
            {0 < impactedAttributesCount && (
              <>
                <Helper level="error">
                  {translate('pim_enrich.entity.attribute_group.mass_delete.attribute_warning', {
                    number_of_attribute: impactedAttributesCount,
                    impactedAttributesCount,
                  })}
                </Helper>
                <Field
                  label={translate('pim_enrich.entity.attribute_group.mass_delete.select_attribute_group', {
                    number_of_attribute: impactedAttributesCount,
                    impactedAttributesCount,
                  })}
                >
                  <SelectInput
                    emptyResultLabel={translate('pim_enrich.entity.attribute_group.mass_delete.empty_result_label')}
                    onChange={(value: string | null) => setReplacementAttributeGroup(value)}
                    placeholder={translate('pim_enrich.entity.attribute_group.mass_delete.placeholder')}
                    value={replacementAttributeGroup}
                    openLabel={translate('pim_enrich.entity.attribute_group.mass_delete.open_label')}
                  >
                    {availableTargetAttributeGroups.map(attributeGroup => (
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
