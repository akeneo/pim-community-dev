import React, {useState} from 'react';
import styled from 'styled-components';
import {useMutation} from 'react-query';
import {Button, Field, Helper, TextInput} from 'akeneo-design-system';
import {SupplierPortalLogo, UnauthenticatedContainer} from '../../components';
import {FormattedMessage} from 'react-intl';
import {resetPassword} from './api/resetPassword';

const ResetPassword = () => {
    const [email, setEmail] = useState('');
    const [isFormSubmitted, setIsFormSubmitted] = useState(false);
    const [hasError, setHasError] = useState(false);

    const mutation = useMutation(resetPassword);

    const submit = async (email: string) => {
        setIsFormSubmitted(true);
        try {
            await mutation.mutateAsync({email: email});
        } catch (error) {
            setHasError(true);
        }
    };

    return (
        <UnauthenticatedContainer>
            <StyledSupplierPortalLogo />

            {!isFormSubmitted ? (
                <>
                    {hasError && (
                        <Helper level="error">
                            <FormattedMessage
                                defaultMessage="An error occurred during the form submission, please try again."
                                id="Z7xDKR"
                            />
                        </Helper>
                    )}
                    <ResetPasswordMessage>
                        <FormattedMessage
                            defaultMessage="Please enter your email address to reset your password."
                            id="ShbYD9"
                        />
                    </ResetPasswordMessage>
                    <Field label={'Email'}>
                        <TextInput onChange={setEmail} value={email} />
                    </Field>

                    <SubmitButton
                        disabled={'' === email}
                        type="button"
                        onClick={async () => await submit(email)}
                        data-testid="submit-button"
                    >
                        <FormattedMessage defaultMessage="Reset my password" id="OXLLjP" />
                    </SubmitButton>
                </>
            ) : (
                <div>
                    <FormattedMessage
                        defaultMessage="If the email address exists, an email has been sent to reset your password."
                        id="WZVD1c"
                    />
                </div>
            )}
        </UnauthenticatedContainer>
    );
};

const ResetPasswordMessage = styled.p`
    margin-bottom: 30px;
`;

const SubmitButton = styled(Button)`
    margin-top: 50px;
`;

const StyledSupplierPortalLogo = styled(SupplierPortalLogo)`
    margin-bottom: 30px;
`;

export {ResetPassword};
