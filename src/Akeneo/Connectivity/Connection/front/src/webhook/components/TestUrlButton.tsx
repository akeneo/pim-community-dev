import React, {FC, SyntheticEvent} from 'react';
import {GhostButton} from '../../common';
import styled from '../../common/styled-with-theme';
import {Translate} from '../../shared/translate';
import {LoaderIcon} from 'akeneo-design-system';

type Props = {
    onClick: (event: SyntheticEvent) => void;
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
    display: flex;
    flex: 1 0 auto;
    margin-left: 10px;
`;

const Loader = styled(LoaderIcon)`
    height: 32px;
    margin-right: 5px;
    width: 13px;
`;
