import React, {FC, useContext} from 'react';
import {useHistory} from 'react-router';
import {HelperLink, InlineHelper, Section, SmallHelper} from '../../common';
import {ConnectionCredentials as ConnectionCredentialsModel} from '../../model/connection-credentials';
import {TranslateContext, Translate, TranslateInterface} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {Credential, CredentialList} from './credentials/Credential';
import {RegenerateButton} from './RegenerateButton';
import {useDateFormatter} from '../../shared/formatter/use-date-formatter';
import {WrongCredentialsCombination} from '../../model/wrong-credentials-combinations';
import styled from 'styled-components';

type Props = {
    code: string;
    label: string;
    credentials: ConnectionCredentialsModel;
    wrongCombination?: WrongCredentialsCombination;
};

export const ConnectionCredentials: FC<Props> = ({code, label, credentials: credentials, wrongCombination}: Props) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();

    return (
        <>
            <Section title={<Translate id='akeneo_connectivity.connection.edit_connection.credentials.title' />} />
            <div>
                <SmallHelper>
                    <Translate
                        id='akeneo_connectivity.connection.edit_connection.credentials.helper.message'
                        placeholders={{label}}
                    />
                    &nbsp;
                    <HelperLink
                        href='https://help.akeneo.com/pim/articles/manage-your-connections.html#grab-your-credentials'
                        target='_blank'
                        rel='noopener noreferrer'
                    >
                        <Translate id='akeneo_connectivity.connection.edit_connection.credentials.helper.link' />
                    </HelperLink>
                </SmallHelper>
                {wrongCombination && (
                    <SmallHelper level={'warning'}>
                        {1 === Object.values(wrongCombination.users).length ? (
                            <OneWrongCombinationWarning
                                lastLogin={Object.values(wrongCombination.users)[0]}
                                goodUsername={credentials.username}
                                translate={translate}
                            />
                        ) : (
                            <SeveralWrongCombinationsWarning
                                combinations={wrongCombination}
                                goodUsername={credentials.username}
                                translate={translate}
                            />
                        )}
                    </SmallHelper>
                )}
            </div>

            <CredentialList>
                <CopiableCredential label={translate('akeneo_connectivity.connection.connection.client_id')}>
                    {credentials.clientId}
                </CopiableCredential>
                <CopiableCredential
                    label={translate('akeneo_connectivity.connection.connection.secret')}
                    actions={
                        <RegenerateButton onClick={() => history.push(`/connections/${code}/regenerate-secret`)} />
                    }
                >
                    {credentials.secret}
                </CopiableCredential>
                <CopiableCredential label={translate('akeneo_connectivity.connection.connection.username')}>
                    {credentials.username}
                </CopiableCredential>
                {credentials.password ? (
                    <CopiableCredential
                        label={translate('akeneo_connectivity.connection.connection.password')}
                        helper={
                            <InlineHelper warning>
                                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.clear_password_helper.message' />{' '}
                                <a
                                    href='https://help.akeneo.com/pim/articles/manage-your-connections.html#grab-your-credentials'
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_connection.credentials.clear_password_helper.link' />
                                </a>
                            </InlineHelper>
                        }
                    >
                        {credentials.password}
                    </CopiableCredential>
                ) : (
                    <Credential
                        label={translate('akeneo_connectivity.connection.connection.password')}
                        helper={
                            <InlineHelper info>
                                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.password_helper.message' />{' '}
                                <a
                                    href='https://help.akeneo.com/pim/articles/manage-your-connections.html#grab-your-credentials'
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_connection.credentials.password_helper.link' />
                                </a>
                            </InlineHelper>
                        }
                        actions={
                            <RegenerateButton
                                onClick={() => history.push(`/connections/${code}/regenerate-password`)}
                            />
                        }
                    >
                        {'••••••••'}
                    </Credential>
                )}
            </CredentialList>
        </>
    );
};

const OneWrongCombinationWarning = ({
    lastLogin,
    goodUsername,
    translate,
}: {
    lastLogin: {
        username: string;
        date: string;
    };
    goodUsername: string;
    translate: TranslateInterface;
}) => {
    const formatDate = useDateFormatter();

    return (
        <>
            <span
                dangerouslySetInnerHTML={{
                    __html: translate(
                        'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.single',
                        {
                            wrong_username: `
                        <span class='AknConnectivityConnection-helper--highlight'>
                            ${lastLogin.username}
                        </span>`,
                            date: formatDate(lastLogin.date, {month: 'short', day: 'numeric'}),
                            time: formatDate(lastLogin.date, {hour: '2-digit', minute: '2-digit', second: '2-digit'}),
                            good_username: `<span class='AknConnectivityConnection-helper--highlight'>${goodUsername}</span>`,
                        }
                    ),
                }}
            />
            <div>
                <HelperLink
                    href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#why-should-you-use-the-connection-username'
                    target='_blank'
                    rel='noopener noreferrer'
                >
                    <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.link' />
                </HelperLink>
            </div>
        </>
    );
};

const ListItem = styled.div`
    margin-top: 3px;
`;

const SeveralWrongCombinationsWarning = ({
    combinations,
    goodUsername,
    translate,
}: {
    combinations: WrongCredentialsCombination;
    goodUsername: string;
    translate: TranslateInterface;
}) => {
    const formatDate = useDateFormatter();

    return (
        <>
            <span
                dangerouslySetInnerHTML={{
                    __html: translate(
                        'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.several',
                        {
                            good_username: `<span class='AknConnectivityConnection-helper--highlight'>${goodUsername}</span>`,
                        }
                    ),
                }}
            />
            &nbsp;
            <HelperLink
                href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#why-should-you-use-the-connection-username'
                target='_blank'
                rel='noopener noreferrer'
            >
                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.link' />
            </HelperLink>
            <div>
                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.list' />
            </div>
            {Object.values(combinations.users).map(lastLogin => {
                return (
                    <ListItem key={lastLogin.username}>
                        <span
                            dangerouslySetInnerHTML={{
                                __html: translate(
                                    'akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.username_date',
                                    {
                                        wrong_username: `
                                    <span class='AknConnectivityConnection-helper--highlight'>
                                        ${lastLogin.username}
                                    </span>`,
                                        date: formatDate(lastLogin.date, {month: 'short', day: 'numeric'}),
                                        time: formatDate(lastLogin.date, {
                                            hour: '2-digit',
                                            minute: '2-digit',
                                            second: '2-digit',
                                        }),
                                    }
                                ),
                            }}
                        />
                    </ListItem>
                );
            })}
        </>
    );
};
