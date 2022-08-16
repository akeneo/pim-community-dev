import React, {FC, PropsWithChildren} from 'react';
import {Helper} from 'akeneo-design-system';
import {CriterionErrors} from '../models/CriterionErrors';

type Props = {
    errors: CriterionErrors;
};

const ErrorHelpers: FC<PropsWithChildren<Props>> = ({errors}) => {
    const errorHelpers = Object.keys(errors)
        .filter(key => errors[key] !== undefined)
        .map(key => {
            return (
                <Helper key={key} level='error'>
                    {errors[key]}
                </Helper>
            );
        });

    if (0 === errorHelpers.length) {
        return null;
    }

    return <>{errorHelpers}</>;
};

export {ErrorHelpers};
