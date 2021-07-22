import React, {FC, useState} from 'react';
import {Modal, AttributesIllustration, Button, Field, TextInput, Locale, Helper} from "akeneo-design-system";
import {LabelCollection, useTranslate, useUserContext} from "@akeneo-pim-community/shared";
import styled from "styled-components";

const FieldSet = styled.div`
  & > * {
    margin-top: 20px;
  }
`

type CreateAttributeModalProps = {
  onConfirm: (data: { code: string, label: string }) => void;
  onClose: () => void;
  defaultCode?: string;
}

const CreateAttributeModal: FC<CreateAttributeModalProps> = ({
  onConfirm,
  onClose,
  defaultCode,
}) => {
  const translate = useTranslate();
  const userContext = useUserContext();

  const [code, setCode] = useState<string>(defaultCode || '');
  const [label, setLabel] = useState<string>('');
  const [isCodeDirty, setCodeDirty] = useState<boolean>(false);
  const [isLabelDirty, setLabelDirty] = useState<boolean>(false);

  const codeViolations: string[] = [];
  if (code === '') {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.must_be_filled'));
  }
  if (code !== '' && !/^[a-zA-Z0-9_]+$/.exec(code)) {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.invalid'));
  }
  if (code !== '' && (
    /^(id|family)$/i.exec(code) ||
    /^(associationTypes|categories|categoryId|completeness|enabled|groups|associations|products|scope|treeId|values|category|parent|label|.*_products|.*_groups|entity_type|attributes)$/.exec(code))
  ) {
    codeViolations.push(translate('pim_enrich.entity.attribute.property.code.not_available'));
  }

  const handleConfirm = () => {
    onClose();
    onConfirm({
      code,
      label,
    });
  }

  const handleCodeChange = (code: string) => {
    setCode(code);
    setCodeDirty(true);
  }

  const handleLabelChange = (label: string) => {
    setLabel(label);
    setLabelDirty(true);
    if (!isCodeDirty) {
      const code = label.replace(/[^a-zA-Z0-9_]/gi, '_').substring(0, 255);
      setCode(code);
    }
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
      <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
        <TextInput
          characterLeftLabel={translate(
            'pim_common.characters_left',
            {count: 255 - code.length},
            100 - code.length
          )}
          value={code}
          onChange={handleCodeChange}
          maxLength={255}
        />
        {(isCodeDirty || isLabelDirty) && codeViolations.map((violation, i) => <Helper key={i} level="error">{violation}</Helper>)}
      </Field>
    </FieldSet>
    <Modal.BottomButtons>
      <Button level="tertiary" onClick={onClose}>
        {translate('pim_common.cancel')}
      </Button>
      <Button level="primary" onClick={handleConfirm} disabled={codeViolations.length > 0}>
        {translate('pim_common.confirm')}
      </Button>
    </Modal.BottomButtons>
  </Modal>
}

export {CreateAttributeModal};
