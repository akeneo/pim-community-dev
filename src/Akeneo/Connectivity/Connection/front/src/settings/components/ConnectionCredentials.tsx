import React, {FC, useContext} from 'react';
import {useHistory} from 'react-router';
import {HelperLink, Section, SmallHelper} from '../../common';
import {ConnectionCredentials as ConnectionCredentialsModel} from '../../model/connection-credentials';
import {TranslateContext, Translate} from '../../shared/translate';
import {CopiableCredential} from './credentials/CopiableCredential';
import {Credential, CredentialList} from './credentials/Credential';
import {RegenerateButton} from './RegenerateButton';
import {WrongCredentialsCombination} from '../../model/wrong-credentials-combinations';
import {WrongCombinationsWarning} from './wrong-credentials/WrongCombinationsWarning';
import {Helper, Link} from 'akeneo-design-system';

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
                    <SmallHelper warning>
                        <WrongCombinationsWarning username={credentials.username} wrongCombination={wrongCombination} />
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
                            <Helper inline level='warning'>
                                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.clear_password_helper.message' />{' '}
                                <Link
                                    href='https://help.akeneo.com/pim/articles/manage-your-connections.html#grab-your-credentials'
                                    target='_blank'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_connection.credentials.clear_password_helper.link' />
                                </Link>
                            </Helper>
                        }
                    >
                        {credentials.password}
                    </CopiableCredential>
                ) : (
                    <Credential
                        label={translate('akeneo_connectivity.connection.connection.password')}
                        helper={
                            <Helper inline level='info'>
                                <Translate id='akeneo_connectivity.connection.edit_connection.credentials.password_helper.message' />{' '}
                                <Link
                                    href='https://help.akeneo.com/pim/articles/manage-your-connections.html#grab-your-credentials'
                                    target='_blank'
                                    rel='noopener noreferrer'
                                >
                                    <Translate id='akeneo_connectivity.connection.edit_connection.credentials.password_helper.link' />
                                </Link>
                            </Helper>
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
