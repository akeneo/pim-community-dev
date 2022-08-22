import React, {FC} from 'react';
import {ScopableLocaleInput} from './ScopableLocaleInput';
import {AnyLocaleInput} from './AnyLocaleInput';

type LocalizableCriterionState = {
    scope: string | null;
    locale: string | null;
};

type Props = {
    state: LocalizableCriterionState;
    onChange: (state: LocalizableCriterionState) => void;
    isInvalid: boolean;
    isScopable: boolean;
};

const LocaleInput: FC<Props> = ({state, onChange, isInvalid, isScopable}) => {
    return isScopable ? (
        <ScopableLocaleInput state={state} onChange={onChange} isInvalid={isInvalid} />
    ) : (
        <AnyLocaleInput state={state} onChange={onChange} isInvalid={isInvalid} />
    );
};

export {LocaleInput};
