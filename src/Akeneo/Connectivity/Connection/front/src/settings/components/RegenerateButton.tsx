import React, {FC, useContext} from 'react';
import {IconButton} from '../../common';
import {TranslateContext} from '../../shared/translate';
import {RefreshIcon} from 'akeneo-design-system';

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
            <RefreshIcon size={18} />
        </IconButton>
    );
};
