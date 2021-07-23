import React, {FC, FunctionComponentElement, useState} from 'react';
import {Modal, AttributesIllustration, Button, Field, TextInput, Locale} from "akeneo-design-system";
import {useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import styled from "styled-components";
import {useAttributeCodeInput} from "../../hooks/attributes/useAttributeCodeInput";

const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`

export type CreateAttributeModalExtraFieldProps = {}
export type CreateAttributeModalExtraField = {
  component: FunctionComponentElement<CreateAttributeModalExtraFieldProps>;
  valid: boolean;
};

type CreateAttributeModalProps = {
  onConfirm: (data: { code: string, label: string }) => void;
  onClose: () => void;
  defaultCode?: string;
  extraFields: CreateAttributeModalExtraField[];
}

const CreateAttributeModal: FC<CreateAttributeModalProps> = ({
  onConfirm,
  onClose,
  defaultCode,
  extraFields,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const [label, setLabel] = useState<string>('');
  const [code, CodeField, isCodeValid] = useAttributeCodeInput({defaultCode, generatedFromLabel: label});

  const handleConfirm = () => {
    onClose();
    onConfirm({
      code,
      label,
    });
  }

  const handleLabelChange = (label: string) => {
    setLabel(label);
  }

  return <Modal closeTitle={translate('pim_common.close')} onClose={onClose} illustration={<AttributesIllustration />}>
    <Modal.SectionTitle color="brand">
      {translate('pim_enrich.entity.attribute.module.create.button')}
    </Modal.SectionTitle>
    <Modal.Title>{translate('pim_common.create')}</Modal.Title>
    <FieldSet>
      <Field label={translate('pim_common.label')} locale={<Locale code={userContext.get('catalogLocale')}/>}>
        <TextInput
          value={label}
          onChange={handleLabelChange}
          maxLength={100}
          characterLeftLabel={translate(
            'pim_common.characters_left',
            {count: 100 - label.length},
            100 - label.length
          )}
        />
      </Field>
      {CodeField}
      {extraFields.map((field, i) => React.cloneElement<CreateAttributeModalExtraFieldProps>(field.component, { key: i }))}
    </FieldSet>
    <Modal.BottomButtons>
      <Button level="tertiary" onClick={onClose}>
        {translate('pim_common.cancel')}
      </Button>
      <Button level="primary" onClick={handleConfirm} disabled={!isCodeValid || extraFields.some(field => !field.valid)}>
        {translate('pim_common.confirm')}
      </Button>
    </Modal.BottomButtons>
  </Modal>
}

export {CreateAttributeModal};
