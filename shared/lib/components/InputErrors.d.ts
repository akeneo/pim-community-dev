import { ValidationError } from '../models/validation-error';
declare type InputErrorsProps = {
    errors?: ValidationError[];
};
declare const InputErrors: ({ errors }: InputErrorsProps) => JSX.Element | null;
export { InputErrors };
