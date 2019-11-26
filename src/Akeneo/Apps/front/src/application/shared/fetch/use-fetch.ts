import {useEffect, useState} from 'react';
import fetch from './fetch';
import {await, Result} from './result';

export const useFetch = <T, E = unknown>(input: RequestInfo, init?: RequestInit) => {
    const [result, setResult] = useState<Result<T, E>>(await());

    useEffect(() => {
        fetch<T, E>(input, init).then(setResult);
    }, [input, init]);

    return result;
};
