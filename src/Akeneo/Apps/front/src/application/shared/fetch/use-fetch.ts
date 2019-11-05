import {useEffect, useState} from 'react';
import fetch from './fetch';
import {ok, Result} from './result';

export const useFetch = <T, E>(input: RequestInfo, init?: RequestInit): Result<T | undefined, E> => {
    const [result, setResult] = useState<Result<T | undefined, E>>(ok(undefined));

    useEffect(() => {
        fetch<T, E>(input, init).then(setResult);
    }, [input, init]);

    return result;
};
