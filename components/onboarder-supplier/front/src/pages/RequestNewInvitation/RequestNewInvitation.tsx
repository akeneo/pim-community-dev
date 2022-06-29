import React, {useState} from 'react';
import styled from 'styled-components';
import {useMutation} from 'react-query';
import {Button, Field, Helper, TextInput} from 'akeneo-design-system';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';
import {FormattedMessage} from 'react-intl';
import {requestNewInvitation} from './api/requestNewInvitation';
import {BadRequestError} from '../../api/BadRequestError';
import {NotFoundError} from '../../api/NotFoundError';

const RequestNewInvitation = () => {
    const [email, setEmail] = useState('');
    const [isFormSubmitted, setIsFormSubmitted] = useState(false);
    const [hasError, setHasError] = useState(false);

    const mutation = useMutation(requestNewInvitation);

    const submit = async (email: string) => {
        setIsFormSubmitted(true);
        try {
            await mutation.mutateAsync({email: email});
        } catch (error) {
            if (error instanceof BadRequestError || error instanceof NotFoundError) {
                setHasError(true);
            }
        }
    };

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo />

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
                    <InvitationHasExpiredMessage>
                        <FormattedMessage
                            defaultMessage="Your invitation has expired. Please enter your email address to receive a new one."
                            id="sYYIml"
                        />
                    </InvitationHasExpiredMessage>
                    <Field label={'Email'}>
                        <TextInput onChange={setEmail} value={email} data-testid="email-input" />
                    </Field>

                    <SubmitButton type="button" onClick={async () => await submit(email)} data-testid="submit-button">
                        <FormattedMessage defaultMessage="Receive a new invitation" id="tq8W8G" />
                    </SubmitButton>
                </>
            ) : (
                <div>
                    <FormattedMessage
                        defaultMessage="An email will be send in a few moments. Please check your emails to access the service."
                        id="SHw26+"
                    />
                </div>
            )}
        </UnauthenticatedContainer>
    );
};

const InvitationHasExpiredMessage = styled.p`
    margin-bottom: 30px;
`;

const SubmitButton = styled(Button)`
    margin-top: 50px;
`;

export {RequestNewInvitation};
