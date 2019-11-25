import {err, ok, Result} from './result';

const defaultInit: RequestInit = {
    credentials: 'include',
    referrerPolicy: 'same-origin',
};

export default async <T, E>(input: RequestInfo, init?: RequestInit): Promise<Result<T, E>> => {
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
};
