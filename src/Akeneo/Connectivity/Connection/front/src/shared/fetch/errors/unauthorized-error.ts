export class UnauthorizedError extends Error {
    constructor() {
        super();
        Object.setPrototypeOf(this, UnauthorizedError.prototype);
    }
}
