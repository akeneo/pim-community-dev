import React from 'react';
import {useForm} from 'react-hook-form';
import {Button, Field, Helper, Modal, TextInput} from 'akeneo-design-system';
import {useRouter, useTranslate} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {duplicateUser} from '../infrastructure/UserDuplicater';
import {UserId} from '../models';

const FormContainer = styled.form`
  & > * {
    margin: 0 10px 20px 0;
  }
`;

type BackendErrors = {[key: string]: string[]};

type CreateUserFormProps = {
  userId: UserId;
  onCancel: () => void;
  onSuccess: (userId: UserId) => void;
  onError: () => void;
};

const CreateUserForm = ({userId, onCancel, onSuccess, onError}: CreateUserFormProps) => {
  const {register, handleSubmit, errors, watch} = useForm({mode: 'onBlur'});
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
          if (typeof data.values === 'undefined' || !Array.isArray(data.values)) {
            console.error('Unable to handle the HTTP response.');
          }

          data.values.forEach((errorValue: any) => {
            if (typeof errorValue.path === 'undefined' || typeof errorValue.message === 'undefined') {
              return;
            }
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

  const allInputsAreFilled = (): boolean => {
    const watchAllFields = watch();

    return (
      Object.keys(watchAllFields).length > 0 &&
      Object.values(watchAllFields).reduce((result: boolean, value: any) => result && value !== '', true)
    );
  };
  const getInputType = (fieldName: string): string => {
    if (['password', 'password_repeat'].includes(fieldName)) {
      return 'password';
    }
    return 'email' === fieldName ? 'email' : 'text';
  };
  const getAutoComplete = (fieldName: string): string => {
    if (['password', 'password_repeat'].includes(fieldName)) {
      return 'new-password';
    }
    return 'username' === fieldName ? 'off' : 'on';
  };

  const shouldNotContainSpace = (value: string): true | string =>
    /\s/.test(value) ? translate('pim_user_management.form.error.should_not_contain_space') : true;
  const shouldContain3Characters = (value: string): true | string =>
    value.length < 3 ? translate('pim_user_management.form.error.too_short_value', {count: 3}) : true;

  const getRegisterParameters = (fieldName: string): {[key: string]: any} => {
    const parameters: {[key: string]: any} = {required: translate('pim_user_management.form.error.required')};
    if ('username' === fieldName) {
      parameters.validate = {shouldNotContainSpace, shouldContain3Characters};
    }
    return parameters;
  };

  return (
    <FormContainer onSubmit={handleSubmit(onSubmit)} data-testid="form-create-user">
      {['username', 'password', 'password_repeat', 'first_name', 'last_name', 'email'].map(
        (fieldName: string, key: number) => (
          <Field
            key={key}
            label={translate('pim_user_management.entity.user.properties.' + fieldName)}
            requiredLabel={translate('pim_common.required_label')}
          >
            <TextInput
              type={getInputType(fieldName)}
              name={fieldName}
              placeholder={translate('pim_user_management.entity.user.properties.' + fieldName)}
              onChange={() => {}}
              ref={register(getRegisterParameters(fieldName))}
              invalid={!!errors[fieldName] || Object.prototype.hasOwnProperty.call(backendErrors, fieldName)}
              required={true}
              autoComplete={getAutoComplete(fieldName)}
            />
            {errors[fieldName] && <Helper level="error">{errors[fieldName].message}</Helper>}
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
        <Button type="submit" disabled={!allInputsAreFilled() || Object.keys(errors).length > 0}>
          {translate('pim_common.confirm')}
        </Button>
      </Modal.BottomButtons>
    </FormContainer>
  );
};

export {CreateUserForm};
