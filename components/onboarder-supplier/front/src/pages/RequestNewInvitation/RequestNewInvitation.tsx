import React, {useState} from 'react';
import styled from 'styled-components';
import {useMutation} from 'react-query';
import {Button, Field, TextInput} from 'akeneo-design-system';
import {OnboarderLogo, UnauthenticatedContainer} from '../../components';
import {FormattedMessage} from 'react-intl';
import {requestNewInvitation} from './api/requestNewInvitation';
import {useToaster} from '../../utils/toaster';
import {useIntl} from 'react-intl';

const RequestNewInvitation = () => {
    const [email, setEmail] = useState('');
    const notify = useToaster();
    const intl = useIntl();

    const mutation = useMutation(requestNewInvitation);

    const submit = async (email: string) => {
        try {
            await mutation.mutateAsync({email: email});
            notify(
                intl.formatMessage({
                    id: '6xalb8',
                    defaultMessage:
                        'Your new request invitation have been taken into account, you should have received a new email.',
                }),
                'success'
            );
        } catch (error) {
            console.error(error);
            // @todo
        }
    };

    return (
        <UnauthenticatedContainer>
            <OnboarderLogo />
            <InvitationHasExpiredMessage>
                <FormattedMessage
                    defaultMessage="Your invitation has expired. Please enter your email address to receive a new one."
                    id="sYYIml"
                />
            </InvitationHasExpiredMessage>
            <Field label={'Email'}>
                <TextInput onChange={setEmail} value={email} data-testid="email-input" />
            </Field>

            <SubmitButton type="button" onClick={async () => await submit(email)}>
                <FormattedMessage defaultMessage="Receive a new invitation" id="tq8W8G" />
            </SubmitButton>
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
