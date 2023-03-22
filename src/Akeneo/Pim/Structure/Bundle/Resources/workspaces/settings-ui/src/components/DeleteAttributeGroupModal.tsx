import React, {useState} from 'react';
import {useBooleanState, Helper, SelectInput, Field} from 'akeneo-design-system';
import {useAttributeGroups, useDeleteAttributeGroup} from '../hooks/attribute-groups';
import {
  DeleteModal,
  NotificationLevel,
  getLabel,
  useTranslate,
  useNotify,
  useUserContext,
  useRouter,
} from '@akeneo-pim-community/shared';

type DeleteAttributeGroupModalProps = {
  attributeGroupCode: string;
};

const DeleteAttributeGroupModal = ({attributeGroupCode}: DeleteAttributeGroupModalProps) => {
  const translate = useTranslate();
  const router = useRouter();
  const notify = useNotify();

  const [isDeleteModalOpen, openDeleteModal, closeDeleteModal] = useBooleanState(false);
  const [replacementAttributeGroupCode, setReplacementAttributeGroupCode] = useState<string | null>(null);
  const catalogLocale = useUserContext().get('catalogLocale');

  const [isLoading, deleteAttributeGroup] = useDeleteAttributeGroup();
  const [attributeGroups] = useAttributeGroups();
  const attributeGroup = attributeGroups.find(attributeGroup => attributeGroup.code === attributeGroupCode);
  if (!attributeGroup) return null;

  const availableReplacementAttributeGroups = attributeGroups.filter(
    attributeGroup => attributeGroup.code !== attributeGroupCode
  );

  const handleOpenDeleteModal = () => {
    setReplacementAttributeGroupCode(null);
    openDeleteModal();
  };

  const handleConfirm = async () => {
    if (isLoading) return;

    try {
      await deleteAttributeGroup(attributeGroup.code, replacementAttributeGroupCode);
      notify(NotificationLevel.INFO, translate('pim_enrich.entity.attribute_group.flash.delete.success'));
      router.redirect(router.generate('pim_enrich_attributegroup_index'));
      closeDeleteModal();
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_enrich.entity.attribute_group.flash.delete.fail'));
    }
  };

  return (
    <>
      <button className="AknDropdown-menuLink delete" onClick={handleOpenDeleteModal}>
        {translate('pim_common.delete')}
      </button>
      {isDeleteModalOpen && (
        <DeleteModal
          title={translate('pim_enrich.entity.attribute_group.plural_label')}
          canConfirmDelete={
            !isLoading && (null !== replacementAttributeGroupCode || 0 === attributeGroup.attribute_count)
          }
          onConfirm={() => handleConfirm()}
          onCancel={closeDeleteModal}
        >
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
                  emptyResultLabel={translate('pim_enrich.entity.attribute_group.delete.empty_result_label')}
                  onChange={(value: string | null) => setReplacementAttributeGroupCode(value)}
                  placeholder={translate('pim_enrich.entity.attribute_group.delete.placeholder')}
                  value={replacementAttributeGroupCode}
                  openLabel={translate('pim_enrich.entity.attribute_group.delete.open_label')}
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
        </DeleteModal>
      )}
    </>
  );
};

export {DeleteAttributeGroupModal};
