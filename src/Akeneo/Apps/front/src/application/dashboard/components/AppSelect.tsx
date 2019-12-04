import React, {useMemo} from 'react';
import {App} from '../../../domain/apps/app.interface';
import {Select2} from '../../common';

interface Props {
    apps: App[];
    code: string;
    onChange: (code?: string) => void;
}

export const AppSelect = ({apps, code, onChange}: Props) => {
    const configuration = useMemo(
        () => ({
            minimumResultsForSearch: -1,
            data: apps.map(({code, label}) => ({id: code, text: label})),
        }),
        [apps]
    );

    return <Select2 configuration={configuration} value={code} onChange={onChange} />;
};
