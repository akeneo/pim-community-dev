import {Source} from './Source';

export type SourceErrors = {
    [key in keyof Source]?: string | null;
};
