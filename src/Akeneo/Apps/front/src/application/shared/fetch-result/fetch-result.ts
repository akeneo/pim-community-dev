import {err, ok, Result} from './result';

const defaultInit: RequestInit = {
    credentials: 'include',
    referrerPolicy: 'same-origin',
};

export const fetchResult = async <T, E>(input: RequestInfo, init?: RequestInit): Promise<Result<T, E>> => {
    const response = await fetch(input, {
        ...defaultInit,
        ...init,
    });
    if (!response.ok) {
        return err<never, E>(await response.json());
    }

    return ok<T>(await response.json());
};
