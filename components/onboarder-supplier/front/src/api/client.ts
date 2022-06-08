import {QueryClient} from "react-query";
import {UnauthorizedError} from "./UnauthorizedError";

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            retry: false,
            refetchOnWindowFocus: false,
            onError: (error) => {
                if (error instanceof UnauthorizedError) {
                    window.location.href = '/login';
                }
            }
        },
        mutations: {
            retry: false,
            onError: (error) => {
                if (error instanceof UnauthorizedError) {
                    window.location.href = '/login';
                }
            }
        },
    },
});

export {queryClient};
