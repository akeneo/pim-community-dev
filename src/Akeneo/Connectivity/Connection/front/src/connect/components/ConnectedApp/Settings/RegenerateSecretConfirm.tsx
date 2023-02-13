import React, {useCallback, useState} from 'react';
import {Button, KeyIcon, Modal, SettingsIllustration, UserIcon} from 'akeneo-design-system';
import {useHistory, useParams} from 'react-router';
import {useTranslate} from '../../../../shared/translate';
import {NotificationLevel, useNotify} from '../../../../shared/notify';
import {useRouter} from '../../../../shared/router/use-router';
import styled from 'styled-components';
import {CopiableCredential} from '../../../../settings/components/credentials/CopiableCredential';
import {isErr} from '../../../../shared/fetch-result/result';
import {useCustomAppRegenerateSecret} from '../../../hooks/use-custom-app-regenerate-secret';
import {useConnectedApp} from '../../../hooks/use-connected-app';
import fn = jest.fn;
import {useQueryClient} from 'react-query';

const Description = styled.div`
    font-size: 17px;
    max-width: 460px;
`;

type Props = {
    handleRedirect: () => void;
    handleRegenerate: () => void;
    buttonDisabled: boolean;
};

export const RegenerateSecretConfirm = ({handleRedirect, handleRegenerate, buttonDisabled}: Props) => {
    const translate = useTranslate();

    return (
        <>
            <Modal.Title>
                {translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.title'
                )}
            </Modal.Title>
            <Description>
                {translate(
                    'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.description'
                )}
            </Description>
            <Modal.BottomButtons>
                <Button level='tertiary' onClick={() => handleRedirect()}>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.cancel_button'
                    )}
                </Button>
                <Button disabled={buttonDisabled} level='danger' onClick={() => handleRegenerate()}>
                    {translate(
                        'akeneo_connectivity.connection.connect.connected_apps.edit.settings.credentials.regenerate_secret.confirm.regenerate_button'
                    )}
                </Button>
            </Modal.BottomButtons>
        </>
    );
};
