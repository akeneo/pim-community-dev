import React from 'react';
import {useForm} from 'react-hook-form';
import {Button, Modal, UsersIllustration, Field, TextInput, SectionTitle, Title, Helper} from 'akeneo-design-system';
import {useRouter, useTranslate, useNotify, NotificationLevel} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';

const FormContainer = styled.form`
  & > * {
    margin: 0 10px 20px 0;
  }
`;

interface IndexProps {
  userId: number;
  onCancel: () => void;
  onDuplicateSuccess: (userId: string) => void;
}

type BackendErrors = {[key: string]: string[]};

const DuplicateUserApp = ({userId, onCancel, onDuplicateSuccess}: IndexProps) => {
  const translate = useTranslate();
  const {register, handleSubmit, errors} = useForm();
  const router = useRouter();
  const notify = useNotify();
  const [backendErrors, setBackendErrors] = React.useState<BackendErrors>({});

  const onSubmit = async (data: any): Promise<any> => {
    setBackendErrors({});
    const url = router.generate('pim_user_user_rest_duplicate', {identifier: userId});
    let response: Response | null;
    try {
      response = await fetch(url, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        method: 'POST',
        body: JSON.stringify(data),
      });
    } catch (error) {
      notify(NotificationLevel.ERROR, translate('pim_user_management.form.duplication.notification.failure'));
      return error;
    }

    if (response.ok) {
      notify(NotificationLevel.SUCCESS, translate('pim_user_management.form.duplication.notification.success'));
      response.json().then((data: any) => {
        onDuplicateSuccess(data.meta.id);
      });
    } else {
      notify(NotificationLevel.ERROR, translate('pim_user_management.form.duplication.notification.failure'));
      response.json().then((data: any) => {
        const newBackendErrors: BackendErrors = {};
        data.values.forEach((errorValue: {path: string; message: string}) => {
          if (!Object.prototype.hasOwnProperty.call(newBackendErrors, errorValue.path)) {
            newBackendErrors[errorValue.path] = [];
          }
          newBackendErrors[errorValue.path].push(errorValue.message);
        });
        setBackendErrors(newBackendErrors);
      });
    }
    return response;
  };

  return (
    <Modal closeTitle={translate('pim_common.close')} onClose={onCancel} illustration={<UsersIllustration />}>
      <SectionTitle color={'brand'} size={'bigger'}>
        {translate('pim_menu.item.user')}
      </SectionTitle>
      <Title>{translate('pim_common.duplicate')}</Title>
      <FormContainer onSubmit={handleSubmit(onSubmit)}>
        {['username', 'password', 'password_repeat', 'first_name', 'last_name', 'email'].map(
          (fieldName: string, key: number) => (
            <Field
              key={key}
              label={translate('pim_user_management.entity.user.properties.' + fieldName)}
              requiredLabel={translate('pim_common.required_label')}
            >
              <TextInput
                type={['password', 'password_repeat'].includes(fieldName) ? 'password' : 'text'}
                name={fieldName}
                placeholder={translate('pim_user_management.entity.user.properties.' + fieldName)}
                onChange={() => {}}
                ref={register({required: true})}
                invalid={!!errors[fieldName] || Object.prototype.hasOwnProperty.call(backendErrors, fieldName)}
              />
              {errors[fieldName] && (
                <Helper level="error">{translate('pim_user_management.form.error.required')}</Helper>
              )}
              {(backendErrors[fieldName] ?? []).map((errorMessage: string, key: number) => (
                <Helper key={key} level="error">
                  {errorMessage}
                </Helper>
              ))}
            </Field>
          )
        )}
        <Modal.BottomButtons>
          <Button onClick={onCancel} level={'tertiary'}>
            {translate('pim_common.cancel')}
          </Button>
          <Button type="submit">{translate('pim_common.confirm')}</Button>
        </Modal.BottomButtons>
      </FormContainer>
    </Modal>
  );
};

export default DuplicateUserApp;
