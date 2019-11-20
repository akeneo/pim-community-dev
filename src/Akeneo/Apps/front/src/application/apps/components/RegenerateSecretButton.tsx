import React, {FC, useContext} from 'react';
import {useHistory} from 'react-router';
import {IconButton} from '../../common';
import {UpdateIcon} from '../../common/icons';
import {TranslateContext} from '../../shared/translate';

interface Props {
    code: string;
}

export const RegenerateSecretButton: FC<Props> = ({code}: Props) => {
    const history = useHistory();
    const translate = useContext(TranslateContext);

    return (
        <IconButton
            onClick={() => history.push(`/apps/${code}/regenerate-secret`)}
            title={translate('akeneo_apps.edit_app.credentials.action.regenerate')}
        >
            <UpdateIcon />
        </IconButton>
    );
};
