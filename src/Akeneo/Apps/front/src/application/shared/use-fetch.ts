import {useState, useEffect} from 'react';
import {err, ok, Result} from './result';

const defaultInit: RequestInit = {
    credentials: 'include'
};

export const useFetch = <T, E>(input: RequestInfo, init?: RequestInit): Result<T | undefined, E> => {
    init = {...defaultInit, ...init};

    const [result, setResult] = useState<Result<T | undefined, E>>(ok(undefined));

    useEffect(() => {
        const doFetch = async () => {
            const response = await fetch(input, init);
            if (!response.ok) {
                setResult(err<E>(await response.json()));

                return;
            }
            setResult(ok<T>(await response.json()));
        };
        doFetch();
    }, [input, init]);

    return result;
};
