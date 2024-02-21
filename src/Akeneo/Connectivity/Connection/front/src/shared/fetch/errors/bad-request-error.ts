export class BadRequestError<T> extends Error {
    constructor(public readonly data?: T) {
        super();
        Object.setPrototypeOf(this, BadRequestError.prototype);
    }
}
