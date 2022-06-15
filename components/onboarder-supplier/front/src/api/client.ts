import {QueryClient} from 'react-query';
import {UnauthorizedError} from './UnauthorizedError';

function redirectToLoginPage(error: unknown) {
    if (error instanceof UnauthorizedError) {
        window.location.href = '/login';
    }
}

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            retry: false,
            refetchOnWindowFocus: false,
            onError: redirectToLoginPage,
        },
        mutations: {
            retry: false,
            onError: redirectToLoginPage,
        },
    },
});

export {queryClient};
