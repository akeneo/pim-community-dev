declare type ValidationError = {
    messageTemplate: string;
    parameters: {
        [key: string]: string;
    };
    message: string;
    propertyPath: string;
    invalidValue: any;
    plural?: number;
};
declare const filterErrors: (errors: ValidationError[], propertyPath: string) => ValidationError[];
declare const getErrorsForPath: (errors: ValidationError[], propertyPath: string) => ValidationError[];
declare const formatParameters: (errors: ValidationError[]) => ValidationError[];
declare const partitionErrors: (errors: ValidationError[], conditions: ((item: ValidationError) => boolean)[]) => ValidationError[][];
export { filterErrors, getErrorsForPath, partitionErrors, formatParameters };
export type { ValidationError };
