import {ValidationError} from './ValidationError';

type Validator<T> = (model: T, path: string) => ValidationError[];

export type {Validator};
