import React, {FC} from 'react';
import {PageError} from '../../../common';
import {Translate} from '../../../shared/translate';

const NoError: FC = () => (
    <PageError
        title={<Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.no_error.title' />}
    />
);

export {NoError};
