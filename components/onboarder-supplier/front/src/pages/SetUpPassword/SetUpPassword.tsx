import React, {useState} from 'react';
import styled from 'styled-components';
import {Button, Field, getColor, TextInput} from 'akeneo-design-system';
import {PasswordInput} from './components/PasswordInput';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';

const SetUpPassword = () => {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    const isSubmitButtonDisabled = '' === password || '' === passwordConfirmation || password !== passwordConfirmation;

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo />
            <WelcomeText>
                <p>
                    Hello <SupplierEmail>jimmy@megasupplier.com</SupplierEmail> !
                </p>
                <p>You have been invited to use Akeneo Onboarder.</p>
                <p>Please create your password to access the data onboarding service.</p>
            </WelcomeText>
            <SetUpPasswordForm>
                <StyledField label={'Password'}>
                    <PasswordInput onChange={setPassword} value={password} />
                </StyledField>
                <StyledField label={'Confirm your password'}>
                    <TextInput type="password" onChange={setPasswordConfirmation} value={passwordConfirmation} />
                </StyledField>
                <PasswordRequirements>
                    <p>Your password should follow these requirements :</p>
                    <p>At least 8 caracters</p>
                    <p>At least an upper-case letter</p>
                    <p>At least a lower-case letter</p>
                    <p>At least a number</p>
                    <p>Correct confirmation</p>
                </PasswordRequirements>
                <Button type="button" disabled={isSubmitButtonDisabled}>
                    Create My password
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
