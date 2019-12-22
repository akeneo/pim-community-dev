import React, {FC, useContext} from 'react';
import {useHistory} from 'react-router';
import {HelperLink, InlineHelper, Section, SmallHelper} from '../../common';
import {AppCredentials as AppCredentialsInterface} from '../../domain/apps/app-credentials.interface';
import {Translate, TranslateContext} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {Credential, CredentialList} from './credentials/Credential';
import {RegenerateButton} from './RegenerateButton';

type Props = {
    code: string;
    label: string;
    credentials: AppCredentialsInterface;
};

export const AppCredentials: FC<Props> = ({code, label, credentials: credentials}: Props) => {
    const translate = useContext(TranslateContext);
    const history = useHistory();

    return (
        <>
            <Section title={<Translate id='akeneo_connectivity.connection.edit_app.credentials.title' />} />
            <div>
                <SmallHelper>
                    <Translate
                        id='akeneo_connectivity.connection.edit_app.credentials.helper.message'
                        placeholders={{label}}
                    />
                    &nbsp;
                    <HelperLink
                        href={translate('akeneo_connectivity.connection.edit_app.credentials.helper.link_url')}
                        target='_blank'
                        rel='noopener noreferrer'
                    >
                        <Translate id='akeneo_connectivity.connection.edit_app.credentials.helper.link' />
                    </HelperLink>
                </SmallHelper>
            </div>

            <CredentialList>
                <CopiableCredential label={translate('akeneo_connectivity.connection.connection.client_id')}>
                    {credentials.clientId}
                </CopiableCredential>
                <CopiableCredential
                    label={translate('akeneo_connectivity.connection.connection.secret')}
                    actions={<RegenerateButton onClick={() => history.push(`/apps/${code}/regenerate-secret`)} />}
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
                                <Translate id='akeneo_connectivity.connection.edit_app.credentials.clear_password_helper.message' />{' '}
                                <a
                                    href={translate(
                                        'akeneo_connectivity.connection.edit_app.credentials.clear_password_helper.link_url'
                                    )}
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_app.credentials.clear_password_helper.link' />
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
                                <Translate id='akeneo_connectivity.connection.edit_app.credentials.password_helper.message' />{' '}
                                <a
                                    href={translate(
                                        'akeneo_connectivity.connection.edit_app.credentials.password_helper.link_url'
                                    )}
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_app.credentials.password_helper.link' />
                                </a>
                            </InlineHelper>
                        }
                        actions={<RegenerateButton onClick={() => history.push(`/apps/${code}/regenerate-password`)} />}
                    >
                        <Translate id='akeneo_connectivity.connection.edit_app.credentials.password_placeholder' />
                    </Credential>
                )}
            </CredentialList>
        </>
    );
};
