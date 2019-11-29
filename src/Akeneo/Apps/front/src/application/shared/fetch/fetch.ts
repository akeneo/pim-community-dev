import {err, ok, Result} from './result';

const defaultInit: RequestInit = {
    credentials: 'include',
    referrerPolicy: 'same-origin',
};

// TODO Add 'Error' (or custom 'ServerError') as possible 'E' return type and 'undefined' as possible 'T' return type.
export default async <T, E>(input: RequestInfo, init?: RequestInit): Promise<Result<T, E>> => {
    try {
        const response = await fetch(input, {
            ...defaultInit,
            ...init,
        });
        if (!response.ok) {
            return err<E>(await response.json());
        }

        // Avoid "SyntaxError: Unexpected end of JSON input" when the Body is empty.
        if (response.status === 204) {
            return ok<T>(undefined as any);
        }

        return ok<T>(await response.json());
    } catch (error) {
        return err<E>(error);
    }
};
