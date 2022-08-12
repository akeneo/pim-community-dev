import React, {FC, PropsWithChildren} from 'react';
import {Helper} from 'akeneo-design-system';
import {CriterionErrors} from '../models/CriterionErrors';

type Props = {
    errors: CriterionErrors;
};

const ErrorHelpers: FC<PropsWithChildren<Props>> = ({errors}) => {
    const errorHelpers = Object.keys(errors)
        .map(key => {
            if (errors[key] === undefined) {
                return null;
            }

            return (
                <Helper key={key} level='error'>
                    {errors[key]}
                </Helper>
            );
        })
        .filter(element => null !== element);

    if (0 === errorHelpers.length) {
        return <></>;
    }

    return <>{errorHelpers}</>;
};

export {ErrorHelpers};
