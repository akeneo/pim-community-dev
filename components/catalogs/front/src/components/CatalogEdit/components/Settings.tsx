import React, {FC} from 'react';
import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from '../utils/findFirstError';
import {EnabledInput} from './EnabledInput';

type Settings = {
    enabled: boolean;
};

type Props = {
    settings: Settings;
    errors: CatalogFormErrors;
};

const Settings: FC<Props> = ({settings, errors}) => {
    return (
        <>
            <EnabledInput value={settings.enabled} error={findFirstError(errors, '[enabled]')} />
        </>
    );
};

export {Settings};
