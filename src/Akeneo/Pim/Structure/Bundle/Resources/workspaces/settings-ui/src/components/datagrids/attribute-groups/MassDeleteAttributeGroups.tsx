import React, {useRef, useState} from 'react';
import {AttributeGroup} from '../../../models';
import {Button, useBooleanState, useAutoFocus, Helper} from 'akeneo-design-system';
import {DoubleCheckDeleteModal, useTranslate} from '@akeneo-pim-community/shared';

type MassDeleteAttributeGroupsProps = {
  attributeGroups: AttributeGroup[];
};
const MassDeleteAttributeGroups = ({attributeGroups}: MassDeleteAttributeGroupsProps) => {
  const translate = useTranslate();
  const [isMassDeleteModalOpen, openMassDeleteModal, closeMassDeleteModal] = useBooleanState(false);
  const [numberOfAttribute] = useState<number>(0);
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const handleConfirm = async () => {
    //onConfirm();
  };

  const handleCancel = () => {
    closeMassDeleteModal();
  };

  return (
    <>
      <Button level="danger" onClick={() => openMassDeleteModal()}>
        Delete
      </Button>
      {isMassDeleteModalOpen && null !== attributeGroups && (
        <DoubleCheckDeleteModal
          title={translate('pim_enrich.entity.attribute_group.mass_delete.title')}
          doubleCheckInputLabel={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_phrase', {
            confirmation_word: translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word'),
          })}
          textToCheck={translate('pim_enrich.entity.attribute_group.mass_delete.confirmation_word')}
          onConfirm={handleConfirm}
          onCancel={handleCancel}
        >
          <p>
            {translate(
              'pim_enrich.entity.attribute_group.mass_delete.confirm',
              {assetCount: attributeGroups.length},
              attributeGroups.length
            )}
          </p>
          {numberOfAttribute > 0 && (
            <Helper level={'error'}>
              {translate('pim_enrich.entity.attribute_group.mass_delete.attribute_warning', {
                number_of_attribute: numberOfAttribute,
              })}
            </Helper>
          )}
        </DoubleCheckDeleteModal>
      )}
    </>
  );
};

export {MassDeleteAttributeGroups};
