import {useState, useEffect} from 'react';
import {err, ok, Result} from './result';

export const useFetch = <T, E>(input: RequestInfo, init?: RequestInit): Result<T | undefined, E> => {
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
