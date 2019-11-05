interface Ok<T> {
    readonly data: T;
}
export const ok = <T>(data: T): Ok<T> => ({data});
export const isOk = <T, E>(result: Result<T, E>): result is Ok<T> => 'data' in result;

interface Err<T> {
    readonly error: T;
}
export const err = <T>(error: T): Err<T> => ({error});
export const isErr = <T, E>(result: Result<T, E>): result is Err<E> => 'error' in result;

export type Result<T, E> = Ok<T> | Err<E>;
