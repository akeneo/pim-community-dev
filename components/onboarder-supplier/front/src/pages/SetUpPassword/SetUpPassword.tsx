import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, Field, getColor, TextInput} from 'akeneo-design-system';
import {PasswordInput} from './components/PasswordInput';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';
import {FormattedMessage, useIntl} from 'react-intl';

const SetUpPassword = () => {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const intl = useIntl();

    const isSubmitButtonDisabled = '' === password || '' === passwordConfirmation || password !== passwordConfirmation;

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo />
            <WelcomeText>
                <p>
                    <FormattedMessage
                        id="QjSrIv"
                        defaultMessage="Hello {name} !"
                        values={{
                            name: <SupplierEmail>jimmy@megasupplier.com</SupplierEmail>,
                        }}
                    />
                </p>
                <p>
                    <FormattedMessage id="onXqax" defaultMessage="You have been invited to use Akeneo Onboarder." />
                </p>
                <p>
                    <FormattedMessage
                        defaultMessage="Please create your password to access the data onboarding service."
                        id="i09Jh/"
                    />
                </p>
            </WelcomeText>
            <SetUpPasswordForm>
                <StyledField
                    label={intl.formatMessage({
                        id: '5sg7KC',
                        defaultMessage: 'Password',
                    })}
                >
                    <PasswordInput onChange={setPassword} value={password} />
                </StyledField>
                <StyledField
                    label={intl.formatMessage({
                        id: 'w/ArXE',
                        defaultMessage: 'Confirm your password',
                    })}
                >
                    <TextInput type="password" onChange={setPasswordConfirmation} value={passwordConfirmation} />
                </StyledField>
                <PasswordRequirements>
                    <p>
                        <FormattedMessage
                            defaultMessage="Your password should follow these requirements:"
                            id="kpFvB9"
                        />
                    </p>
                    <p>
                        <FormattedMessage defaultMessage="At least 8 caracters" id="OF6NrW" />
                    </p>
                    <p>
                        <FormattedMessage defaultMessage="At least an upper-case letter" id="ek9x6P" />
                    </p>
                    <p>
                        <FormattedMessage defaultMessage="At least a lower-case letter" id="1GYZg/" />
                    </p>
                    <p>
                        <FormattedMessage defaultMessage="At least a number" id="nAsEaE" />
                    </p>
                    <p>
                        <FormattedMessage defaultMessage="Correct confirmation" id="XurM/d" />
                    </p>
                </PasswordRequirements>
                <Button type="button" disabled={isSubmitButtonDisabled}>
                    <FormattedMessage defaultMessage="Create My password" id="d8nJr6" />
                </Button>
            </SetUpPasswordForm>
        </UnauthenticatedContainer>
    );
};

const WelcomeText = styled.div`
    margin-bottom: 30px;
    color: ${getColor('grey140')};
`;
const SupplierEmail = styled.span`
    color: ${getColor('brand100')};
    font-weight: bold;
`;
const SetUpPasswordForm = styled.form``;
const StyledField = styled(Field)`
    margin-bottom: 20px;
    position: relative;
`;
const PasswordRequirements = styled.div`
    color: ${getColor('grey120')};
    margin-bottom: 50px;

    p:first-child {
        color: ${getColor('grey140')};
    }
    p:not(:first-child) {
        margin-top: 15px;
    }
`;

export {SetUpPassword};
