import React, {useState} from 'react';
import {Button, useBooleanState, Helper, SelectInput, Field} from 'akeneo-design-system';
import {
  DoubleCheckDeleteModal,
  NotificationLevel,
  getLabel,
  useTranslate,
  useNotify,
  useUserContext,
  Section,
} from '@akeneo-pim-community/shared';
import {AttributeGroup, DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP} from '../../../models';
import {useMassDeleteAttributeGroups} from '../../../hooks';

type MassDeleteAttributeGroupsModalProps = {
  impactedAttributeGroups: AttributeGroup[];
  availableReplacementAttributeGroups: AttributeGroup[];
};

const MassDeleteAttributeGroupsModal = ({
  impactedAttributeGroups,
  availableReplacementAttributeGroups,
}: MassDeleteAttributeGroupsModalProps) => {
  const translate = useTranslate();
  const notify = useNotify();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [replacementAttributeGroup, setReplacementAttributeGroup] = useState<string>(
    DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP
  );

  const catalogLocale = useUserContext().get('catalogLocale');

  const [isLoading, launchMassDeleteAttributeGroups] = useMassDeleteAttributeGroups();
  const impactedAttributesCount = impactedAttributeGroups.reduce(
    (totalCount, {attribute_count}) => totalCount + attribute_count,
    0
  );

  const handleOpenMassDeleteModal = () => {
    setReplacementAttributeGroup(DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP);
    openMassDeleteModal();
  };

  const handleLaunchMassDelete = async () => {
    if (isLoading) return;

    try {
      await launchMassDeleteAttributeGroups(impactedAttributeGroups, replacementAttributeGroup);
      notify(NotificationLevel.INFO, translate('pim_enrich.entity.attribute_group.flash.mass_delete.success'));
      closeMassDeleteModal();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute_group.flash.mass_delete.fail'));
    }
  };

  return (
    <>
      <Button level="danger" onClick={handleOpenMassDeleteModal}>
        {translate('pim_common.delete')}
      </Button>
      {isMassDeleteModalOpen && (
        <DoubleCheckDeleteModal
          title={translate('pim_enrich.entity.attribute_group.plural_label')}
          doubleCheckInputLabel={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase', {
            confirmation_word: translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word'),
          })}
          textToCheck={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word')}
          canConfirmDelete={!isLoading && (null !== replacementAttributeGroup || 0 === impactedAttributesCount)}
          onConfirm={handleLaunchMassDelete}
          onCancel={closeMassDeleteModal}
        >
          <Section>
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
                  {translate(
                    'pim_enrich.entity.attribute_group.mass_delete.attribute_warning',
                    {
                      number_of_attribute: impactedAttributesCount,
                    },
                    impactedAttributesCount
                  )}
                </Helper>
                <Field
                  label={translate(
                    'pim_enrich.entity.attribute_group.mass_delete.select_attribute_group',
                    {
                      number_of_attribute: impactedAttributesCount,
                    },
                    impactedAttributesCount
                  )}
                >
                  <SelectInput
                    clearable={false}
                    emptyResultLabel={translate('pim_enrich.entity.attribute_group.mass_delete.empty_result_label')}
                    onChange={value => setReplacementAttributeGroup(value)}
                    placeholder={translate('pim_enrich.entity.attribute_group.mass_delete.placeholder')}
                    value={replacementAttributeGroup}
                    openLabel={translate('pim_enrich.entity.attribute_group.mass_delete.open_label')}
                  >
                    {availableReplacementAttributeGroups.map(attributeGroup => (
                      <SelectInput.Option key={attributeGroup.code} value={attributeGroup.code}>
                        {getLabel(attributeGroup.labels, catalogLocale, attributeGroup.code)}
                      </SelectInput.Option>
                    ))}
                  </SelectInput>
                </Field>
              </>
            )}
          </Section>
        </DoubleCheckDeleteModal>
      )}
    </>
  );
};

export {MassDeleteAttributeGroupsModal};
