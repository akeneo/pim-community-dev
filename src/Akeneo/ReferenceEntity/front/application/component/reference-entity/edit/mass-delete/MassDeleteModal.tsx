import React, {useState, useRef} from 'react';
import styled from 'styled-components';
import {Field, TextInput, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {DeleteModal} from '@akeneo-pim-community/shared';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/identifier';

type MassDeleteModalProps = {
  onConfirm: () => void;
  onCancel: () => void;
  selectedRecordCount: number;
  referenceEntityIdentifier: ReferenceEntityIdentifier;
};

const SpacedField = styled(Field)`
  margin-top: 20px;
`;

const MassDeleteModal = ({
  onConfirm,
  onCancel,
  referenceEntityIdentifier,
  selectedRecordCount,
}: MassDeleteModalProps) => {
  const translate = useTranslate();
  const [referenceEntityConfirm, setReferenceEntityConfirm] = useState<string>('');
  const isValid = referenceEntityConfirm === referenceEntityIdentifier.stringValue();
  const inputRef = useRef<HTMLInputElement>(null);

  useAutoFocus(inputRef);

  const handleConfirm = async () => {
    if (!isValid) return;

    onConfirm();
  };

  return (
    <DeleteModal
      title={translate('pim_reference_entity.record.mass_delete.title')}
      onConfirm={handleConfirm}
      onCancel={onCancel}
      canConfirmDelete={isValid}
    >
      <p>
        {translate(
          'pim_reference_entity.record.mass_delete.confirm',
          {count: selectedRecordCount},
          selectedRecordCount
        )}
      </p>
      <p>{translate('pim_reference_entity.record.mass_delete.extra_information')}</p>
      <SpacedField
        label={translate('pim_reference_entity.record.mass_delete.confirm_label', {
          referenceEntityIdentifier: referenceEntityIdentifier.stringValue(),
        })}
      >
        <TextInput
          ref={inputRef}
          value={referenceEntityConfirm}
          onChange={setReferenceEntityConfirm}
          onSubmit={handleConfirm}
        />
      </SpacedField>
    </DeleteModal>
  );
};

export {MassDeleteModal};
