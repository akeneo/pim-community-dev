import React, {FC, useCallback, useState} from 'react';
import {Button, FamilyIllustration, Field, Helper, Modal, TextInput} from 'akeneo-design-system';
import styled from 'styled-components';
import {NotificationLevel, useNotify, useTranslate, useRouter, ValidationError} from '@akeneo-pim-community/shared';
import {useCreateFamily} from '../hooks';

type CreateFormProps = {
  onConfirm: () => void;
  onCancel: () => void;
};

const CreateForm: FC<CreateFormProps> = ({onConfirm, onCancel}) => {
  const translate = useTranslate();
  const createFamily = useCreateFamily();
  const notify = useNotify();
  const router = useRouter();

  const [codeValue, setCodeValue] = useState<string>('');
  const [isLoading, setIsLoading] = useState<boolean>(false);
  const [errors, setErrors] = useState<ValidationError[]>([]);

  const handleChangeValue = (value: string) => {
    setCodeValue(value);
  };

  const handleCreateFamily = useCallback(async () => {
    try {
      setIsLoading(true);
      setErrors([]);
      const family = await createFamily(codeValue);

      notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.family.flash.create.success'));
      router.redirectToRoute('pim_enrich_family_edit', {code: family.code});
      onConfirm();
    } catch (error) {
      setErrors(error.values);
    } finally {
      setIsLoading(false);
    }
  }, [codeValue, createFamily, router, errors, isLoading]);

  return (
    <Modal onClose={onCancel} closeTitle={translate('pim_common.cancel')} illustration={<FamilyIllustration />}>
      <Modal.SectionTitle color="brand">{translate('pim_menu.item.family')}</Modal.SectionTitle>
      <Modal.Title>{translate('pim_common.create')}</Modal.Title>
      <Content>
        <Field label={translate('pim_common.code')} requiredLabel={translate('pim_common.required_label')}>
          <TextInput onChange={handleChangeValue} value={codeValue} invalid={0 < errors.length} />
          {errors.map((error, index) => (
            <Helper key={index} inline level="error">
              {translate(error.message, error.parameters)}
            </Helper>
          ))}
        </Field>
        <BtnList>
          <Button onClick={onCancel} level="tertiary">
            {translate('pim_common.cancel')}
          </Button>
          <Button disabled={isLoading} onClick={handleCreateFamily}>
            {translate('pim_common.save')}
          </Button>
        </BtnList>
      </Content>
    </Modal>
  );
};

const Content = styled.div`
  margin-top: 20px;
`;

const BtnList = styled.div`
  display: flex;
  gap: 10px;
  margin-top: 20px;
`;

export {CreateForm};
