import {BadRequestError} from './BadRequestError';
import {ForbiddenError} from './ForbiddenError';
import {UnauthorizedError} from './UnauthorizedError';
import {NotFoundError} from './NotFoundError';

export const apiFetch = async <T, E extends Error = Error>(url: string, init?: RequestInit): Promise<T> => {
    const response = await fetch(url, {
        ...init,
        headers: {
            ...init?.headers,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        switch (response.status) {
            case 400:
            case 422:
                throw new BadRequestError<E>(await response.json());
            case 401:
                throw new UnauthorizedError();
            case 403:
                throw new ForbiddenError();
            case 404:
                throw new NotFoundError();
            default:
                throw new Error(`${response.status} ${response.statusText}`);
        }
    }

    return await response.json();
};
