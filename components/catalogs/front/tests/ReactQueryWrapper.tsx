import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';

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
