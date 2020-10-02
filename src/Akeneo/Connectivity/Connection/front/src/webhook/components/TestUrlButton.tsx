import React, {FC} from 'react';
import {GhostButton} from '../../common';
import {LoaderIcon} from '../../common/icons';
import styled from '../../common/styled-with-theme';
import {Translate} from '../../shared/translate';

type Props = {
    onClick: () => void;
    disabled: boolean;
    loading: boolean;
};

export const TestUrlButton: FC<Props> = ({onClick, disabled, loading}) => {
    return (
        <Button onClick={onClick} disabled={disabled || loading}>
            {loading && <Loader />}
            <Translate id='akeneo_connectivity.connection.webhook.form.test' />
        </Button>
    );
};

const Button = styled(GhostButton)`
    margin-left: 10px;
    display: flex;
    align-items: center;
`;

const Loader = styled(LoaderIcon)`
    width: 32px;
    height: 32px;
    margin-right: 5px;
`;
