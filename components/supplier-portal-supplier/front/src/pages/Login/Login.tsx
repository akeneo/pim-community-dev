import React, {SyntheticEvent, useState} from 'react';
import {SupplierPortalLogo, UnauthenticatedContainer} from '../../components';
import styled from 'styled-components';
import {Button, Field, Helper, Link, TextInput} from 'akeneo-design-system';
import {useAuthenticate} from './hooks/useAuthenticate';
import {FormattedMessage, useIntl} from 'react-intl';
import {routes} from '../routes';
import {useHistory} from 'react-router-dom';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [hasLoginFailed, setHasLoginFailed] = useState(false);
    const intl = useIntl();
    const {login} = useAuthenticate();
    const history = useHistory();

    const isSubmitButtonDisabled = '' === email || '' === password;

    const onSubmit = async (event: any) => {
        event.preventDefault();
        const isSuccess = await login(email, password);
        setHasLoginFailed(!isSuccess);
    };

    const goToResetPassword = (event: SyntheticEvent) => {
        event.preventDefault();

        history.push(routes.resetPassword);
    };

    return (
        <UnauthenticatedContainer>
            <SupplierPortalLogo width={213} />
            <Form method="POST" onSubmit={onSubmit} role="form">
                <EmailInput label={intl.formatMessage({defaultMessage: 'Email', id: 'sy+pv5'})}>
                    <TextInput onChange={setEmail} value={email} invalid={hasLoginFailed} />
                </EmailInput>
                <PasswordInput label={intl.formatMessage({defaultMessage: 'Password', id: '5sg7KC'})}>
                    <TextInput
                        type="password"
                        onChange={setPassword}
                        value={password}
                        title={''}
                        invalid={hasLoginFailed}
                    />
                    {hasLoginFailed && (
                        <Helper level="error">
                            <FormattedMessage
                                defaultMessage="Your email or password seems to be wrong. Please, try again."
                                id="pEIVvb"
                            />
                        </Helper>
                    )}
                </PasswordInput>
                <ForgotPasswordLink>
                    <Link onClick={goToResetPassword}>
                        <FormattedMessage defaultMessage="Forgot your password?" id="cyRU1N" />
                    </Link>
                </ForgotPasswordLink>
                <Button type="submit" disabled={isSubmitButtonDisabled} onClick={onSubmit} data-testid="submit-login">
                    <FormattedMessage defaultMessage="Login" id="AyGauy" />
                </Button>
            </Form>
        </UnauthenticatedContainer>
    );
};

const ForgotPasswordLink = styled.div`
    margin-bottom: 50px;
`;

const Form = styled.form`
    margin-top: 30px;
`;

const EmailInput = styled(Field)`
    margin-bottom: 20px;
    position: relative;
`;

const PasswordInput = styled(Field)`
    margin-bottom: 12px;
    position: relative;
`;

export {Login};
