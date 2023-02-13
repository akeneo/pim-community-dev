import React from 'react';
import {Button, Modal} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';
import styled from 'styled-components';

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
