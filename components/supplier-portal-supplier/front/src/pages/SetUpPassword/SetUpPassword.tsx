import React, {ReactElement, useEffect, useState} from 'react';
import styled from 'styled-components';
import {
    AkeneoThemedProps,
    Button,
    CheckIcon,
    Field,
    getColor,
    Helper,
    TextInput,
    Checkbox,
    Link,
    getFontSize,
} from 'akeneo-design-system';
import {PasswordInput} from './components/PasswordInput';
import {SupplierPortalLogo, UnauthenticatedContainer} from '../../components';
import {FormattedMessage, useIntl} from 'react-intl';
import {useParams} from 'react-router-dom';
import {NotFoundError} from '../../api/NotFoundError';
import {BadRequestError} from '../../api/BadRequestError';
import {NotFound} from '../NotFound';
import {useContributorAccount} from './hooks';
import {RequestNewInvitation} from '../RequestNewInvitation/RequestNewInvitation';

type Params = {
    accessToken: string;
};

const SetUpPassword = () => {
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [isSubmitButtonDisabled, setIsSubmitButtonDisabled] = useState(true);
    const [hasConsentToPrivacyPolicy, setConsentToPrivacyPolicy] = useState(false);
    const intl = useIntl();
    const {accessToken} = useParams<Params>();
    const {loadingError, contributorAccount, submitPassword, passwordHasErrors} = useContributorAccount(accessToken);

    useEffect(() => {
        const isPasswordValid =
            password.match(/(^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,255})$/) && password === passwordConfirmation;
        setIsSubmitButtonDisabled(!isPasswordValid || !hasConsentToPrivacyPolicy);
    }, [password, passwordConfirmation, setIsSubmitButtonDisabled, hasConsentToPrivacyPolicy]);

    if (loadingError instanceof BadRequestError) {
        return <RequestNewInvitation />;
    }

    if (loadingError instanceof NotFoundError) {
        return <NotFound />;
    }

    if (!contributorAccount) {
        return null;
    }

    return (
        <UnauthenticatedContainer>
            <SupplierPortalLogo width={213} />
            <WelcomeText>
                <p>
                    <FormattedMessage
                        id="YxvL5F"
                        defaultMessage="Hello {contributorEmail} !"
                        values={{
                            contributorEmail: <ContributorEmail>{contributorAccount.email}</ContributorEmail>,
                        }}
                    />
                </p>
                <p>
                    <FormattedMessage
                        id="u0BAeP"
                        defaultMessage="You have been invited to use Akeneo Supplier Portal."
                    />
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
                    <PasswordInput
                        data-testid="password-input"
                        onChange={setPassword}
                        value={password}
                        invalid={passwordHasErrors}
                    />
                    {passwordHasErrors && (
                        <Helper level="error">
                            <FormattedMessage id="KpExEC" defaultMessage="Your password is invalid." />
                        </Helper>
                    )}
                </StyledField>
                <StyledField
                    label={intl.formatMessage({
                        id: 'w/ArXE',
                        defaultMessage: 'Confirm your password',
                    })}
                >
                    <TextInput
                        data-testid="confirm-password-input"
                        type="password"
                        onChange={setPasswordConfirmation}
                        value={passwordConfirmation}
                    />
                </StyledField>
                <PasswordRequirements>
                    <p>
                        <FormattedMessage
                            defaultMessage="Your password should follow these requirements:"
                            id="kpFvB9"
                        />
                    </p>
                    <PasswordRule isValid={8 <= password.length}>
                        <FormattedMessage defaultMessage="At least 8 characters" id="YwMziN" />
                    </PasswordRule>
                    <PasswordRule isValid={/[A-Z]/.test(password)}>
                        <FormattedMessage defaultMessage="At least an uppercase letter" id="67ZuXt" />
                    </PasswordRule>
                    <PasswordRule isValid={/[a-z]/.test(password)}>
                        <FormattedMessage defaultMessage="At least a lowercase letter" id="PTGASZ" />
                    </PasswordRule>
                    <PasswordRule isValid={/[0-9]/.test(password)}>
                        <FormattedMessage defaultMessage="At least a number" id="nAsEaE" />
                    </PasswordRule>
                    <PasswordRule isValid={'' !== password && password === passwordConfirmation}>
                        <FormattedMessage defaultMessage="Correct confirmation" id="XurM/d" />
                    </PasswordRule>
                </PasswordRequirements>

                <ConsentToPrivacyPolicyContainer>
                    <Checkbox checked={hasConsentToPrivacyPolicy} onChange={setConsentToPrivacyPolicy}>
                        <FormattedMessage
                            defaultMessage="I consent to the <link>Akeneo Privacy policy</link>."
                            id="HnRQny"
                            values={{
                                link: chunks => (
                                    <Link
                                        onClick={(event: any) => {
                                            event.stopPropagation();
                                        }}
                                        href="https://www.akeneo.com/privacy-policy/"
                                        target="_blank"
                                    >
                                        <strong>{chunks}</strong>
                                    </Link>
                                ),
                            }}
                        />
                    </Checkbox>
                </ConsentToPrivacyPolicyContainer>

                <Button
                    data-testid="submit-button"
                    type="button"
                    disabled={isSubmitButtonDisabled}
                    onClick={async () => await submitPassword(password, hasConsentToPrivacyPolicy)}
                >
                    <FormattedMessage defaultMessage="Create My password" id="d8nJr6" />
                </Button>
            </SetUpPasswordForm>
        </UnauthenticatedContainer>
    );
};

const PasswordRule = ({isValid, children}: {isValid: boolean; children: ReactElement}) => {
    return (
        <PasswordRuleContainer isValid={isValid}>
            {isValid && <CheckIcon size={16} data-testid="password-rule-is-valid" />}
            {children}
        </PasswordRuleContainer>
    );
};

const PasswordRuleContainer = styled.div<{isValid: boolean} & AkeneoThemedProps>`
    color: ${({isValid}) => getColor(isValid ? 'green100' : 'grey120')};
    margin-top: 15px;
    display: flex;
    gap: 5px;
    align-items: center;
`;

const WelcomeText = styled.div`
    margin: 30px 0 30px 0;
    color: ${getColor('grey140')};
`;
const ContributorEmail = styled.span`
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
    margin-bottom: 25px;

    p:first-child {
        color: ${getColor('grey140')};
    }
`;
const ConsentToPrivacyPolicyContainer = styled.div`
    color: ${getColor('grey140')};
    display: flex;
    margin-bottom: 20px;

    a {
        text-decoration: none;
    }
    label {
        font-size: ${getFontSize('default')};
    }
`;

export {SetUpPassword};
