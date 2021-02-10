import React from 'react';
import {useForm} from 'react-hook-form';
import {Button, Field, Helper, Modal, TextInput} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import styled from 'styled-components';
import {duplicateUser} from '../infrastructure/UserDuplicater';

const FormContainer = styled.form`
  & > * {
    margin: 0 10px 20px 0;
  }
`;

type BackendErrors = {[key: string]: string[]};

type CreateUserFormProps = {
  userId: number;
  onCancel: () => void;
  onSuccess: (userId: string) => void;
  onError: () => void;
};

const CreateUserForm = ({userId, onCancel, onSuccess, onError}: CreateUserFormProps) => {
  const {register, handleSubmit, errors} = useForm();
  const router = useRouter();
  const translate = useTranslate();
  const [backendErrors, setBackendErrors] = React.useState<BackendErrors>({});

  const onSubmit = async (data: any): Promise<void> => {
    setBackendErrors({});
    const response = await duplicateUser(router, userId, data);

    if (null !== response && response.ok) {
      response.json().then((data: any) => onSuccess(data.meta.id));
    } else {
      onError();
      if (null !== response) {
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
    }
  };

  return (
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
            {errors[fieldName] && <Helper level="error">{translate('pim_user_management.form.error.required')}</Helper>}
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
  );
};

export {CreateUserForm};
