import React, {FC} from 'react';
import {ClientErrorIllustration} from 'akeneo-design-system';
import {useTranslate} from '../../../../shared/translate';

export const ErrorMonitoringError: FC = () => {
    const translate = useTranslate();

    return (
        <>
            {translate('akeneo_connectivity.connection.connect.connected_apps.edit.error_monitoring.error')}
            <ClientErrorIllustration />
        </>
    );
};
