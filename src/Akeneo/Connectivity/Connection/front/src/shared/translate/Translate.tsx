import React from 'react';
import {useContext} from 'react';
import {TranslateContext} from './translate-context';

interface Props {
    id: string;
    placeholders?: {[name: string]: string};
    count?: number;
}

export const Translate = ({id, placeholders = {}, count = 1}: Props) => {
    const translate = useContext(TranslateContext);

    return <>{translate(id, placeholders, count)}</>;
};
