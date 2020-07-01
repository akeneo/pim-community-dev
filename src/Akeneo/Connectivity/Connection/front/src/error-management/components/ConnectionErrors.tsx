import React, {FC} from 'react';
import {Loading} from '../../common';
import {useConnectionErrors} from '../hooks/api/use-connection-errors';
import {ErrorList} from './ErrorList';
import {ErrorsHelper} from './ErrorsHelper';

type Props = {
    connectionCode: string;
};

const ConnectionErrors: FC<Props> = ({connectionCode}) => {
    const {loading, connectionErrors} = useConnectionErrors(connectionCode);

    if (loading) {
        return <Loading />;
    }

    return (
        <>
            <ErrorsHelper errorCount={connectionErrors.length} />
            <ErrorList errors={connectionErrors} />
        </>
    );
};

export {ConnectionErrors};
