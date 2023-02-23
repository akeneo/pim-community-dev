import {SourceParameterErrors} from './SourceParameterErrors';

export type SourceErrors = {
    source: string | undefined;
    locale: string | undefined;
    scope: string | undefined;
    parameters?: SourceParameterErrors | undefined;
};
