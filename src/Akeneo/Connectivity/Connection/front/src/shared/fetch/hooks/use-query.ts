import {useEffect, useState} from 'react';
import {useRoute} from '../../router';
import {BadRequestError} from '../errors/bad-request-error';
import {NotFoundError} from '../errors/not-found-error';
import {UnauthorizedError} from '../errors/unauthorized-error';

const useQuery = <T, E = unknown>(route: string, params?: {[param: string]: string}) => {
    const url = useRoute(route, params);

    const [{loading, error, data}, setState] = useState<{
        loading: boolean;
        error?: BadRequestError<E> | UnauthorizedError | NotFoundError | Error;
        data?: T;
    }>({
        loading: true,
    });

    useEffect(() => {
        let cancelled = false;

        fetch(url, {
            method: 'GET',
            credentials: 'include',
            headers: {'content-type': 'application/json'},
        })
            .then(async response => {
                if (true === cancelled) {
                    return;
                }

                if (false === response.ok) {
                    switch (response.status) {
                        case 400:
                            throw new BadRequestError<E>(await response.json());
                        case 401:
                            throw new UnauthorizedError();
                        case 404:
                            throw new NotFoundError();
                        default:
                            throw new Error(`${response.status} ${response.statusText}`);
                    }
                }

                setState({loading: false, data: await response.json()});
            })
            .catch(error => {
                setState({loading: false, error});
            });

        return () => {
            cancelled = true;
        };
    }, [url]);

    if (error) {
        throw error;
    }

    return {loading, data};
};

export {useQuery};
