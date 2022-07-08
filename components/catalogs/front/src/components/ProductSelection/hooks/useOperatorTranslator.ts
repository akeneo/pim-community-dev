import {Operator} from '../models/Operator';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useCallback} from 'react';

type OperatorTranslator = (operator: Operator) => string;

export const useOperatorTranslator = (): OperatorTranslator => {
    const translate = useTranslate();

    return useCallback(
        operator => {
            const label = translate(`akeneo_catalogs.product_selection.operators.${operator}`);

            return label.charAt(0).toUpperCase() + label.slice(1);
        },
        [translate]
    );
};
