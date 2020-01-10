import React, {FC, useContext} from 'react';
import {useHistory} from 'react-router';
import {HelperLink, InlineHelper, Section, SmallHelper} from '../../common';
import {ConnectionCredentials as ConnectionCredentialsModel} from '../../model/connection-credentials';
import {Translate, TranslateContext} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {Credential, CredentialList} from './credentials/Credential';
import {RegenerateButton} from './RegenerateButton';

type Props = {
    code: string;
    label: string;
    credentials: ConnectionCredentialsModel;
};

export const ConnectionCredentials: FC<Props> = ({code, label, credentials: credentials}: Props) => {
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
