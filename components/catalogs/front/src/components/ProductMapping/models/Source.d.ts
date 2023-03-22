import {SourceParameter} from './SourceParameter';

export type Source = {
    source: string | null;
    locale: string | null;
    scope: string | null;
    parameters?: SourceParameter;
    default?: string | boolean | null;
};
