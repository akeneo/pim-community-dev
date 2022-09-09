import React, {FC} from 'react';
import {QueryClient, QueryClientProvider} from 'react-query';

const ReactQueryWrapper: FC = ({children}) => {
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                // by default, react query uses a back-off delay gradually applied to each retry attempt.
                // Overriding the default value allows us to test its failing behavior without slowing down
                // the tests.
                retryDelay: 10,
            },
        },
    });

    return (
        <QueryClientProvider client={queryClient}>
            {children}
        </QueryClientProvider>
    );
};

export {ReactQueryWrapper};
