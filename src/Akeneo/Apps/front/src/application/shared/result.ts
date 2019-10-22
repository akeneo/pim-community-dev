interface Ok<T> {
    readonly ok: T;
}

interface Err<T> {
    readonly err: T;
}

export type Result<T, E> = Ok<T> | Err<E>;

export const ok = <T>(ok: T): Ok<T> => ({ok});

export const err = <T>(err: T): Err<T> => ({err});

export const isOk = <T, E>(result: Result<T, E>): result is Ok<T> => 'ok' in result;

export const isErr = <T, E>(result: Result<T, E>): result is Err<E> => 'err' in result;
