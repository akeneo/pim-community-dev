import React, {FC} from 'react';
import {Loading} from '../../common';
import {useConnectionErrors} from '../hooks/api/use-connection-errors';
import {ErrorList} from './ErrorList';
import {ErrorsHelper} from './ErrorsHelper';
import {useFeatureFlags} from '../../shared/feature-flags';

type Props = {
    connectionCode: string;
    description: string;
};

const ConnectionErrors: FC<Props> = ({connectionCode, description}) => {
    const {loading, connectionErrors} = useConnectionErrors(connectionCode);
    const featureFlags = useFeatureFlags();

    if (loading) {
        return <Loading />;
    }

    return (
        <>
            {!featureFlags.isEnabled('free_trial') && (
                <ErrorsHelper errorCount={connectionErrors.length} description={description} />
            )}

            <ErrorList errors={connectionErrors} />
        </>
    );
};

export {ConnectionErrors};
