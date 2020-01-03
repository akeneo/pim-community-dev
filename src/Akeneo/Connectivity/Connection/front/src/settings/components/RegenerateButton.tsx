import React, {FC, useContext} from 'react';
import {IconButton} from '../../common';
import {UpdateIcon} from '../../common/icons';
import {TranslateContext} from '../../shared/translate';

interface Props {
    onClick: () => void;
}

export const RegenerateButton: FC<Props> = ({onClick}: Props) => {
    const translate = useContext(TranslateContext);

    return (
        <IconButton
            onClick={onClick}
            title={translate('akeneo_connectivity.connection.edit_connection.credentials.action.regenerate')}
        >
            <UpdateIcon />
        </IconButton>
    );
};
