import React, {ChangeEvent, FormEvent, useState} from 'react';
import {useParams} from 'react-router-dom';
import styled from 'styled-components';
import Password from './Password';
import {Button, Link, Theme} from 'akeneo-design-system';
import {Input} from './Input';
import {useRouter, useTranslate} from '@akeneo-pim-community/legacy-bridge';

const Form = styled.form<{theme?: Theme}>`
  min-width: 300px;
  @media (max-width: 768px) {
    width: 100%;
  }
`;

const InputBlock = styled.div<{theme?: Theme}>``;

const InputGroup = styled.div<{theme?: Theme}>`
  padding-bottom: 3em;
  width: 100%;
`;

const PasswordReset = styled(Link)<{theme?: Theme}>`
  width: 139px;
  color: ${props => props.theme.color.yellow120};
  font-size: 13px;
  cursor: pointer;

  @media (max-width: 768px) {
    font-size: 15px;
  }
`;

const RedirectNotification = styled.p<{theme?: Theme}>`
  font-size: 15px;
  max-width: 300px;

  @media (max-width: 768px) {
    max-width: 350px;
    font-size: 18px;
  }
  strong {
    color: ${props => props.theme.color.yellow120};
  }
`;

type Params = {
  email: string;
  tenantId: string;
  catalogCode: string;
};

type Props = {
  csrfToken: string;
};

const LoginForm: React.FC<Props> = ({csrfToken}) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [isPasswordVisible, setPasswordVisible] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string>('');
  const params = useParams<Params>();
  const translate = useTranslate();
  const router = useRouter();

  const handleOnSubmit = (e: FormEvent<HTMLFormElement>) => {
    console.log('submitting', e);
    // @todo override submission to handle errors
  };

  const onEmailChange = (e: ChangeEvent<HTMLInputElement>) => setEmail(e.target.value);

  const onPasswordChange = (e: ChangeEvent<HTMLInputElement>) => setPassword(e.target.value);

  return (
    <>
      {params && 'email' in params ? (
        <RedirectNotification>
          {translate('redirect.signup.login', {
            email: `<strong key="${email}">${params && 'email' in params && params['email']}</strong>`,
          })}
        </RedirectNotification>
      ) : null}
      <Form action={router.generate('pim_user_security_check')} method={'POST'} onSubmit={handleOnSubmit}>
        <InputGroup>
          <InputBlock>
            <Input
              name={'_username'}
              status={errorMessage?.length ? 'error' : 'default'}
              type="text"
              title={translate('pim_user.user.login.username_or_email')}
              placeholder={translate('pim_user.user.login.username_or_email')}
              onChange={onEmailChange}
              value={email}
            />
          </InputBlock>
          <InputBlock>
            <Password
              name={'_password'}
              status={errorMessage?.length ? 'error' : 'default'}
              statusMessage={errorMessage}
              isPasswordVisible={isPasswordVisible}
              makePasswordVisible={setPasswordVisible}
              label={translate('pim_user.user.fields.password')}
              placeholder={translate('pim_user.user.fields.password')}
              onChange={onPasswordChange}
            />
          </InputBlock>
          <PasswordReset href={router.generate('pim_user_reset_request')}>
            {translate('pim_user.user.login.password_forgotten')}
          </PasswordReset>
        </InputGroup>

        <input type="hidden" name="_target_path" value="" />
        <input type="hidden" name="_csrf_token" value={csrfToken} />

        <Button disabled={email === '' || password === ''} type={'submit'} name={'_submit'}>
          {translate('pim_user.user.login.log_in')}
        </Button>
      </Form>
    </>
  );
};

export default LoginForm;
