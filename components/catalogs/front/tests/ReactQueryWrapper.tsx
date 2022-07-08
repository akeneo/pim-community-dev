import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';

const ReactQueryWrapper: FC = ({children}) => (
    <QueryClientProvider client={new QueryClient()}>
        {children}
    </QueryClientProvider>
);

export {ReactQueryWrapper};
