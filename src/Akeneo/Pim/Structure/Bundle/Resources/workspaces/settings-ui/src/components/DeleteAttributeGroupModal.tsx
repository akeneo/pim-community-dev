import React, {useState} from 'react';
import {Helper, SelectInput, Field} from 'akeneo-design-system';
import {useAttributeGroups, useDeleteAttributeGroup} from '../hooks/attribute-groups';
import {
  DeleteModal,
  NotificationLevel,
  getLabel,
  useTranslate,
  useNotify,
  useUserContext,
  useRouter,
  Section,
} from '@akeneo-pim-community/shared';
import {DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP} from '../models';

type DeleteAttributeGroupModalProps = {
  attributeGroupCode: string;
  isOpen: boolean;
  onClose: () => void;
};

const DeleteAttributeGroupModal = ({attributeGroupCode, isOpen, onClose}: DeleteAttributeGroupModalProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const notify = useNotify();

  const [replacementAttributeGroupCode, setReplacementAttributeGroupCode] = useState<string>(
    DEFAULT_REPLACEMENT_ATTRIBUTE_GROUP
  );
  const catalogLocale = useUserContext().get('catalogLocale');

  const [isLoading, deleteAttributeGroup] = useDeleteAttributeGroup();
  const [attributeGroups] = useAttributeGroups();
  const attributeGroup = attributeGroups.find(attributeGroup => attributeGroup.code === attributeGroupCode);
  if (!attributeGroup) return null;

  const availableReplacementAttributeGroups = attributeGroups.filter(
    attributeGroup => attributeGroup.code !== attributeGroupCode
  );

  const handleConfirm = async () => {
    if (isLoading) return;

    try {
      await deleteAttributeGroup(attributeGroup.code, replacementAttributeGroupCode);
      notify(NotificationLevel.INFO, translate('pim_enrich.entity.attribute_group.flash.delete.success'));
      router.redirect(router.generate('pim_enrich_attributegroup_index'));
      onClose();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute_group.flash.delete.fail'));
    }
  };

  return (
    <>
      {isOpen && (
        <DeleteModal
          title={translate('pim_enrich.entity.attribute_group.plural_label')}
          canConfirmDelete={
            !isLoading && (null !== replacementAttributeGroupCode || 0 === attributeGroup.attribute_count)
          }
          onConfirm={() => handleConfirm()}
          onCancel={onClose}
        >
          <Section>
            <p>{translate('pim_enrich.entity.attribute_group.delete.confirm')}</p>
            {0 < attributeGroup.attribute_count && (
              <>
                <Helper level="error">
                  {translate('pim_enrich.entity.attribute_group.delete.attribute_warning', {
                    number_of_attribute: attributeGroup.attribute_count,
                  })}
                </Helper>
                <Field
                  label={translate(
                    'pim_enrich.entity.attribute_group.delete.select_attribute_group',
                    {
                      number_of_attribute: attributeGroup.attribute_count,
                    },
                    attributeGroup.attribute_count
                  )}
                >
                  <SelectInput
                    clearable={false}
                    emptyResultLabel={translate('pim_enrich.entity.attribute_group.mass_delete.empty_result_label')}
                    onChange={setReplacementAttributeGroupCode}
                    placeholder={translate('pim_enrich.entity.attribute_group.mass_delete.placeholder')}
                    value={replacementAttributeGroupCode}
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
        </DeleteModal>
      )}
    </>
  );
};

export type {DeleteAttributeGroupModalProps};
export {DeleteAttributeGroupModal};
