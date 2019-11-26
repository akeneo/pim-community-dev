interface Ok<T> {
    readonly data: T;
}
interface Err<T> {
    readonly error: T;
}
interface Await {
    readonly await: true;
}
export type Result<T, E> = Ok<T> | Err<E> | Await;

export const ok = <T>(data: T): Ok<T> => ({data});
export const isOk = <T, E>(result: Result<T, E>): result is Ok<T> => 'data' in result;

export const err = <T>(error: T): Err<T> => ({error});
export const isErr = <T, E>(result: Result<T, E>): result is Err<E> => 'error' in result;

export const await = (): Await => ({await: true});
export const isAwaiting = <T, E>(result: Result<T, E>): result is Await => 'await' in result;
