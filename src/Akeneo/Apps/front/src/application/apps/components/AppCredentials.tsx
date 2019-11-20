import React, {useRef, FC, useContext} from 'react';
import {AppCredentials as AppCredentialsInterface} from '../../../domain/apps/app-credentials.interface';
import {Section, SmallHelper} from '../../common';
import {Translate, TranslateContext} from '../../shared/translate';
import {CredentialList, CredentialListItem} from './CredentialList';
import {RegenerateSecretButton} from './RegenerateSecretButton';
import {IconButton} from '../../common';
import {DuplicateIcon} from '../../common/icons';
import {useNotify, NotificationLevel} from '../../shared/notify';
import styled from 'styled-components';

interface Props {
    code: string;
    appCredentials: AppCredentialsInterface;
}

export const AppCredentials: FC<Props> = ({code, appCredentials}: Props) => {
    const notify = useNotify();
    const translate = useContext(TranslateContext);

    const clientIdRef = useRef<HTMLElement>(null);
    const secretRef = useRef<HTMLElement>(null);

    const handleCopy = (element: HTMLElement) => {
        const selection = window.getSelection();
        if (null === selection) {
            return;
        }

        const range = document.createRange();
        range.selectNodeContents(element);

        selection.removeAllRanges();
        selection.addRange(range);

        document.execCommand('copy');

        selection.removeAllRanges();
    };

    const handleClientIdCopy = () => {
        if (null === clientIdRef.current) {
            return;
        }
        handleCopy(clientIdRef.current);

        notify(NotificationLevel.INFO, translate('akeneo_apps.edit_app.credentials.flash.copied', {name: 'Client ID'}));
    };

    const handleSecretCopy = () => {
        if (null === secretRef.current) {
            return;
        }
        handleCopy(secretRef.current);

        notify(NotificationLevel.INFO, translate('akeneo_apps.edit_app.credentials.flash.copied', {name: 'Secret'}));
    };

    return (
        <>
            <Section title={<Translate id='akeneo_apps.edit_app.credentials.title' />} />
            <div>
                <SmallHelper>
                    <Translate id='akeneo_apps.edit_app.credentials.helper' />
                </SmallHelper>
            </div>

            <CredentialList>
                <CredentialListItem
                    label={<Translate id='akeneo_apps.app.client_id' />}
                    action={
                        <IconButton
                            onClick={handleClientIdCopy}
                            title={translate('akeneo_apps.edit_app.credentials.action.copy')}
                        >
                            <DuplicateIcon />
                        </IconButton>
                    }
                >
                    <span ref={clientIdRef}>{appCredentials.clientId}</span>
                </CredentialListItem>
                <CredentialListItem
                    label={<Translate id='akeneo_apps.app.secret' />}
                    action={
                        <ActionList>
                            <IconButton
                                onClick={handleSecretCopy}
                                title={translate('akeneo_apps.edit_app.credentials.action.copy')}
                            >
                                <DuplicateIcon />
                            </IconButton>
                            <RegenerateSecretButton code={code} />
                        </ActionList>
                    }
                >
                    <span ref={secretRef}>{appCredentials.secret}</span>
                </CredentialListItem>
            </CredentialList>
        </>
    );
};

const ActionList = styled.div`
    > * {
        margin: 0 5px;

        :first-child {
            margin-left: 0;
        }
        :last-child {
            margin-right: 0;
        }
    }
`;
