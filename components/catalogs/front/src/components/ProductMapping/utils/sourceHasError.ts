import {SourceErrors} from '../models/SourceErrors';

const hasError = (errors: object | undefined): boolean => {
    if (errors === undefined) {
        return false;
    }

    return (
        Object.entries(errors).filter(([, value]) => {
            if (typeof value === 'object' && hasError(value)) {
                return true;
            }
            return typeof value === 'string';
        }).length > 0
    );
};

export const sourceHasError = (errors: SourceErrors | undefined): boolean => hasError(errors);
