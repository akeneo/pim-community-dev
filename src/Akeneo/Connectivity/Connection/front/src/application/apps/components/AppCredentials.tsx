import React, {FC, useContext} from 'react';
import {AppCredentials as AppCredentialsInterface} from '../../../domain/apps/app-credentials.interface';
import {InlineHelper, Section, SmallHelper, HelperLink} from '../../common';
import {Translate, TranslateContext} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {Credential, CredentialList} from './credentials/Credential';
import {RegenerateSecretButton} from './RegenerateSecretButton';

type Props = {
    code: string;
    label: string;
    credentials: AppCredentialsInterface;
};

export const AppCredentials: FC<Props> = ({code, label, credentials: credentials}: Props) => {
    const translate = useContext(TranslateContext);

    return (
        <>
            <Section title={<Translate id='akeneo_apps.edit_app.credentials.title' />} />
            <div>
                <SmallHelper>
                    <Translate id='akeneo_apps.edit_app.credentials.helper.message' placeholders={{label}} />
                    &nbsp;
                    <HelperLink
                        href={translate('akeneo_apps.edit_app.credentials.helper.link_url')}
                        target='_blank'
                        rel='noopener noreferrer'
                    >
                        <Translate id='akeneo_apps.edit_app.credentials.helper.link' />
                    </HelperLink>
                </SmallHelper>
            </div>

            <CredentialList>
                <CopiableCredential label={translate('akeneo_apps.app.client_id')}>
                    {credentials.clientId}
                </CopiableCredential>
                <CopiableCredential
                    label={translate('akeneo_apps.app.secret')}
                    actions={<RegenerateSecretButton code={code} />}
                >
                    {credentials.secret}
                </CopiableCredential>
                <CopiableCredential label={translate('akeneo_apps.app.username')}>
                    {credentials.username}
                </CopiableCredential>
                {credentials.password ? (
                    <CopiableCredential
                        label={translate('akeneo_apps.app.password')}
                        helper={
                            <InlineHelper warning>
                                <Translate id='akeneo_apps.edit_app.credentials.clear_password_helper.message' />{' '}
                                <a
                                    href={translate('akeneo_apps.edit_app.credentials.clear_password_helper.link_url')}
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_apps.edit_app.credentials.clear_password_helper.link' />
                                </a>
                            </InlineHelper>
                        }
                    >
                        {credentials.password}
                    </CopiableCredential>
                ) : (
                    <Credential
                        label={translate('akeneo_apps.app.password')}
                        helper={
                            <InlineHelper info>
                                <Translate id='akeneo_apps.edit_app.credentials.password_helper.message' />{' '}
                                <a
                                    href={translate('akeneo_apps.edit_app.credentials.password_helper.link_url')}
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_apps.edit_app.credentials.password_helper.link' />
                                </a>
                            </InlineHelper>
                        }
                    >
                        <Translate id='akeneo_apps.edit_app.credentials.password_placeholder' />
                    </Credential>
                )}
            </CredentialList>
        </>
    );
};
