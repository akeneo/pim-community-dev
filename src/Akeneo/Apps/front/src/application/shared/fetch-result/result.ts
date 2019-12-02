export interface Ok<E> {
    readonly value: E;
}

export interface Err<A> {
    readonly error: A;
}

export type Result<T, E> = Ok<T> | Err<E>;

export const ok = <T = never, E = never>(value: T): Result<T, E> => ({value});

export const err = <T = never, E = never>(error: E): Result<T, E> => ({error});

export const isOk = <T, E>(result: Result<T, E>): result is Ok<T> => 'value' in result;

export const isErr = <T, E>(result: Result<T, E>): result is Err<E> => 'error' in result;
