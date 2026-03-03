import {Violation} from './Violation';

type Validator<T> = (model: T, path: string) => Violation[];

export type {Validator};
