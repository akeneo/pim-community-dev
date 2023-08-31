import React, {useCallback, useMemo, useState} from 'react';
import {AttributesIllustration, Button, Field, Helper, Modal, TextInput, useAutoFocus} from 'akeneo-design-system';
import {IdentifierGenerator, TEXT_TRANSFORMATION} from '../models';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {Styled} from '../components/Styled';
import {useGetIdentifierGenerators, useValidateFormWithEnter} from '../hooks';

type CreateGeneratorModalProps = {
  onClose: () => void;
  onSave: (value: IdentifierGenerator) => void;
};

const CreateGeneratorModal: React.FC<CreateGeneratorModalProps> = ({onClose, onSave}) => {
  const [label, setLabel] = useState<string>('');
  const [code, setCode] = useState<string>('');
  const [isCodeDirty, setIsCodeDirty] = useState(false);
  const {data: generators = []} = useGetIdentifierGenerators();

  const translate = useTranslate();
  const userContext = useUserContext();
  const uiLocale = userContext.get('uiLocale');
  const labelLengthLimit = 255;
  const codeLengthLimit = 100;

  const isCodeEmpty = useMemo(() => '' === code, [code]);
  const isCodeAlreadyUsed = useMemo(
    () => !!generators?.find(({code: existingCode}) => code.toLowerCase() === existingCode.toLowerCase()),
    [generators, code]
  );
  const isFormInvalid = useMemo(() => isCodeAlreadyUsed || isCodeEmpty, [isCodeEmpty, isCodeAlreadyUsed]);

  const labelInputRef = React.useRef<HTMLInputElement | null>(null);
  useAutoFocus(labelInputRef);

  const onLabelChange = useCallback(
    (value: string) => {
      setLabel(value);
      if (!isCodeDirty)
        setCode(
          value
            .trim()
            .replace(/[^a-zA-Z0-9]/g, '_')
            .substring(0, codeLengthLimit)
        );
    },
    [isCodeDirty]
  );

  const onCodeChange = useCallback(value => {
    setIsCodeDirty('' !== value.trim());
    setCode(value);
  }, []);

  const onConfirm = useCallback(() => {
    if (!isFormInvalid) {
      onSave({
        code,
        target: '',
        labels: label.trim() ? {[uiLocale]: label.trim()} : {},
        conditions: [],
        structure: [],
        delimiter: null,
        text_transformation: TEXT_TRANSFORMATION.NO,
      });
    }
  }, [isFormInvalid, onSave, code, uiLocale, label]);

  useValidateFormWithEnter(onConfirm);

  return (
    <Modal closeTitle={translate('pim_common.close')} illustration={<AttributesIllustration />} onClose={onClose}>
      <Modal.SectionTitle color="brand">{translate('pim_title.akeneo_identifier_generator_index')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_identifier_generator.create.form.title')}</Modal.Title>
      <Styled.FormContainer>
        <Field label={translate('pim_common.label')} locale={uiLocale}>
          <TextInput
            name="label"
            value={label}
            onChange={onLabelChange}
            maxLength={labelLengthLimit}
            ref={labelInputRef}
          />
        </Field>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput name="code" value={code} onChange={onCodeChange} maxLength={codeLengthLimit} />
          {isCodeAlreadyUsed && <Helper level="error">{translate('validation.create.code_already_used')}</Helper>}
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
