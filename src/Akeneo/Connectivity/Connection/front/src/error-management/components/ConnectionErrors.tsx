import React, {FC} from 'react';
import {Loading} from '../../common';
import {useConnectionErrors} from '../hooks/api/use-connection-errors';
import {ErrorList} from './ErrorList';
import {ErrorsHelper} from './ErrorsHelper';
import {useFeatureFlags} from '../../shared/feature-flags';

type Props = {
    connectionCode: string;
};

const ConnectionErrors: FC<Props> = ({connectionCode}) => {
    const {loading, connectionErrors} = useConnectionErrors(connectionCode);
    const featureFlags = useFeatureFlags();

    if (loading) {
        return <Loading />;
    }

    return (
        <>
            {!featureFlags.isEnabled('free_trial') && <ErrorsHelper errorCount={connectionErrors.length} />}
            <ErrorList errors={connectionErrors} />
        </>
    );
};

export {ConnectionErrors};
