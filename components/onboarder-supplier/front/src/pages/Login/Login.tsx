import React, {useState} from 'react';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';
import styled from 'styled-components';
import {Button, Field, TextInput} from 'akeneo-design-system';

const Login = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');

    const isSubmitButtonDisabled = '' === email || '' === password;

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo />
            <div>
                <StyledField label={'Email'}>
                    <TextInput onChange={setEmail} value={email} />
                </StyledField>
                <StyledField label={'Password'}>
                    <TextInput type="password" onChange={setPassword} value={password} />
                </StyledField>
                <Button type="button" disabled={isSubmitButtonDisabled}>
                    Login
                </Button>
            </div>
        </UnauthenticatedContainer>
    );
};

const StyledField = styled(Field)`
    margin-bottom: 20px;
    position: relative;
`;

export {Login};
