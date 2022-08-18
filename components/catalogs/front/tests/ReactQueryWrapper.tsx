import React, {FC} from 'react';
import {QueryClient, QueryClientProvider, setLogger} from 'react-query';

setLogger({
    log: console.log,
    warn: console.warn,
    error: () => {/* no logging output in console on error for tests */},
});

const ReactQueryWrapper: FC = ({children}) => {
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                retry: false,
            },
        }
    });

    return (
        <QueryClientProvider client={queryClient}>
            {children}
        </QueryClientProvider>
    );
};

export {ReactQueryWrapper};
