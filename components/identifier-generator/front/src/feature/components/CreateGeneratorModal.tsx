import React, {useCallback, useState} from 'react';
import {AttributesIllustration, Button, Field, Modal, TextInput} from 'akeneo-design-system';
import {IdentifierGenerator} from '../../models';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Styled} from './Styled';

type GeneratorCreationProps = {
  onClose: () => void;
  onSave: (value: IdentifierGenerator) => void;
};

const CreateGeneratorModal: React.FC<GeneratorCreationProps> = ({onClose, onSave}) => {
  const [label, setLabel] = useState('');
  const [code, setCode] = useState('');
  const [isCodeDirty, setIsCodeDirty] = useState(false);

  const translate = useTranslate();
  const userContext = useUserContext();
  const uiLocale = userContext.get('uiLocale');

  const onLabelChange = useCallback(
    (value: string) => {
      setLabel(value);
      if (!isCodeDirty) setCode(value.replace(/[^a-zA-Z0-9]/g, '_'));
    },
    [isCodeDirty]
  );

  const onCodeChange = useCallback(value => {
    setIsCodeDirty(true);
    setCode(value);
  }, []);

  const onConfirm = useCallback(() => {
    onSave({
      code: code,
      labels: {[uiLocale]: label},
    });
  }, [code, label, onSave, uiLocale]);

  const isFormInvalid = React.useMemo(() => code === '', [code]);

  return (
    <Modal closeTitle={translate('pim_common.close')} illustration={<AttributesIllustration />} onClose={onClose}>
      <Modal.SectionTitle color="brand">{translate('pim_title.akeneo_identifier_generator_index')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_identifier_generator.create.form.title')}</Modal.Title>
      <Styled.FormContainer>
        <Field label={translate('pim_common.label')} locale={uiLocale}>
          <TextInput name="label" value={label} onChange={onLabelChange} />
        </Field>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput name="code" value={code} onChange={onCodeChange} />
        </Field>
      </Styled.FormContainer>
      <Modal.BottomButtons>
        <Button onClick={onClose} level="tertiary">
          {translate('pim_common.cancel')}
        </Button>
        <Button onClick={onConfirm} disabled={isFormInvalid}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateGeneratorModal};
