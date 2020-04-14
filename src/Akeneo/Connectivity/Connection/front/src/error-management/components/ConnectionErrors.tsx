import React, {FC} from 'react';
import {useConnectionErrors} from '../hooks/api/use-connection-errors';
import {ErrorList} from './ErrorList';
import {ErrorsHelper} from './ErrorsHelper';
import {NoError} from './NoError';

type Props = {
    connectionCode: string;
};

const ConnectionErrors: FC<Props> = ({connectionCode}) => {
    const {loading, connectionErrors} = useConnectionErrors(connectionCode);

    if (loading) {
        return <>Loading...</>; // TODO Loading spinner
    }

    return (
        <>
            <ErrorsHelper errorCount={connectionErrors.length} />
            {connectionErrors.length > 0 ? <ErrorList errors={connectionErrors} /> : <NoError />}
        </>
    );
};

export {ConnectionErrors};
