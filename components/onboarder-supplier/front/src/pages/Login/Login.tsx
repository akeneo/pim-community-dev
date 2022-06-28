import React, {useState} from 'react';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';
import styled from 'styled-components';
import {Button, Field, Helper, TextInput} from 'akeneo-design-system';
import {useAuthenticate} from './hooks/useAuthenticate';
import {FormattedMessage, useIntl} from 'react-intl';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [hasLoginFailed, setHasLoginFailed] = useState(false);
    const intl = useIntl();
    const {login} = useAuthenticate();

    const isSubmitButtonDisabled = '' === email || '' === password;

    const onSubmit = async () => {
        const isSuccess = await login(email, password);
        setHasLoginFailed(!isSuccess);
    };

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo width={213} />
            <Content>
                <StyledField label={intl.formatMessage({defaultMessage: 'Email', id: 'sy+pv5'})}>
                    <TextInput onChange={setEmail} value={email} invalid={hasLoginFailed} />
                </StyledField>
                <StyledField label={intl.formatMessage({defaultMessage: 'Password', id: '5sg7KC'})}>
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
                </StyledField>
                <Button type="button" disabled={isSubmitButtonDisabled} onClick={onSubmit} data-testid="submit-login">
                    <FormattedMessage defaultMessage="Login" id="AyGauy" />
                </Button>
            </Content>
        </UnauthenticatedContainer>
    );
};

const Content = styled.div`
    margin-top: 30px;
`;

const StyledField = styled(Field)`
    margin-bottom: 20px;
    position: relative;
`;

export {Login};
