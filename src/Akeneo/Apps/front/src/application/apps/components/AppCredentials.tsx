import React, {FC, useContext} from 'react';
import {AppCredentials as AppCredentialsInterface} from '../../../domain/apps/app-credentials.interface';
import {Section, SmallHelper} from '../../common';
import {Translate, TranslateContext} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {CredentialList} from './credentials/CredentialList';
import {RegenerateSecretButton} from './RegenerateSecretButton';
import {Credential} from './credentials/Credential';

interface Props {
    code: string;
    credentials: AppCredentialsInterface;
}

export const AppCredentials: FC<Props> = ({code, credentials: credentials}: Props) => {
    const translate = useContext(TranslateContext);

    return (
        <>
            <Section title={<Translate id='akeneo_apps.edit_app.credentials.title' />} />
            <div>
                <SmallHelper>
                    <Translate id='akeneo_apps.edit_app.credentials.helper' />
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
                <Credential label={translate('akeneo_apps.app.password')}>{credentials.password}</Credential>
            </CredentialList>
        </>
    );
};
