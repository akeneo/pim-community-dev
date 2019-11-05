import {err, ok, Result} from './result';

const defaultInit: RequestInit = {
    credentials: 'include',
};

export default async <T, E>(input: RequestInfo, init?: RequestInit): Promise<Result<T | undefined, E>> => {
    const response = await fetch(input, {...defaultInit, ...init});
    if (!response.ok) {
        return err<E>(await response.json());
    }

    if (200 === response.status) {
        return ok<T>(await response.json());
    }

    return ok<undefined>(undefined);
};
