import React, {useCallback, useState} from 'react';
import {AttributesIllustration, Button, Field, Modal, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {IdentifierGenerator} from '../../models';
import {useTranslate, useUserContext} from '@akeneo-pim-community/shared';

const BreadCrumb = styled.label`
  color: #9452ba;
  text-transform: uppercase;
  margin-bottom: 10px;
`;

const Title = styled.div`
  font-size: 28px;
  color: #11324d;
  line-height: 150%;
  margin-bottom: 40px;
`;

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
`;

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

  const onClick = useCallback(() => {
    onSave({
      code: code,
      labels: {[uiLocale]: label},
    });
  }, [code, label, onSave, uiLocale]);

  const isFormInvalid = React.useMemo(() => code === '', [code]);

  return (
    <Modal
      closeTitle={translate('pim_identifier_generator.create.form.close')}
      illustration={<AttributesIllustration />}
      onClose={onClose}
    >
      <BreadCrumb>Settings / identifier generator</BreadCrumb>
      <Title>{translate('pim_identifier_generator.create.form.title')}</Title>
      <FormContainer>
        <Field label={translate('pim_identifier_generator.create.form.label')} locale={uiLocale}>
          <TextInput name="label" value={label} onChange={onLabelChange} />
        </Field>
        <Field label={translate('pim_identifier_generator.create.form.code')}>
          <TextInput name="code" value={code} onChange={onCodeChange} />
        </Field>
      </FormContainer>
      <Modal.BottomButtons>
        <Button onClick={onClose} level="tertiary">
          {translate('pim_identifier_generator.create.form.cancel')}
        </Button>
        <Button onClick={onClick} disabled={isFormInvalid}>
          {translate('pim_identifier_generator.create.form.confirm')}
        </Button>
      </Modal.BottomButtons>
    </Modal>
  );
};

export {CreateGeneratorModal};
