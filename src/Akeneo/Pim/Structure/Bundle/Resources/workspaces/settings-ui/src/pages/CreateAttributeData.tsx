import React, {FunctionComponentElement} from 'react';
import {useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import {useAttributeCodeInput} from "../hooks/attributes/useAttributeCodeInput";
import {AttributesIllustration, Button, Field, Locale, Modal, TextInput} from "akeneo-design-system";
import {CreateAttributeButtonStepProps} from "../../../../../../../../Platform/Bundle/UIBundle/Resources/public/js/attribute/form/CreateAttributeButtonApp";
import styled from "styled-components";

const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`
type CreateAttributeModalExtraFieldProps = {}
type CreateAttributeModalExtraField = {
  data: {[key: string]: any};
  component: FunctionComponentElement<CreateAttributeModalExtraFieldProps>;
  valid: boolean;
};

type CreateAttributeDataProps = CreateAttributeButtonStepProps & {
  extraFields?: CreateAttributeModalExtraField[];
}

const CreateAttributeData: React.FC<CreateAttributeDataProps> = ({
  onClose,
  onStepConfirm,
  initialData,
  extraFields= [],
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const [label, setLabel] = React.useState<string>(initialData?.label || '');
  const [code, CodeField, isCodeValid] = useAttributeCodeInput({defaultCode: initialData?.code, generatedFromLabel: label});

  const handleConfirm = () => {
    const extraFieldsData = extraFields.reduce((old, extraField) => {
      return {...old, ...extraField.data};
    }, {});
    onStepConfirm({code, label, ...extraFieldsData});
  }

  return <Modal closeTitle={translate('pim_common.close')} onClose={onClose} illustration={<AttributesIllustration/>}>
    <Modal.SectionTitle color="brand">
      {translate('pim_enrich.entity.attribute.module.create.button')}
    </Modal.SectionTitle>
    <Modal.Title>{translate('pim_common.create')}</Modal.Title>
    <FieldSet>
      <Field label={translate('pim_common.label')} locale={<Locale code={userContext.get('catalogLocale')}/>}>
        <TextInput
          value={label}
          onChange={setLabel}
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

export {CreateAttributeData};
