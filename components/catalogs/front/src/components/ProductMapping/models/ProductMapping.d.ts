import {Source} from './Source';

export type ProductMapping = {
    (key: string): Source
}|{};
