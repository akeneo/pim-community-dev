import React, {FC} from 'react';
import {ProductSelectionValues} from '../models/ProductSelectionValues';
import {getColor, getFontSize} from 'akeneo-design-system';
import {useCountProductsInSelectionCriteria} from '../hooks/useCountProductsInSelectionCriteria';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const Container = styled.div`
    border-right: 1px solid ${getColor('grey', 100)};
    padding: 5px 20px 5px 0;
    margin: 0 20px 0 0;
    text-align: center;
`;

const Count = styled.span`
    color: ${getColor('brand', 100)};
    font-size: ${getFontSize('default')};
`;

type Props = {
    criteria: ProductSelectionValues;
};

export const CountSelectedProductsByCriteria: FC<Props> = ({criteria}) => {
    const translate = useTranslate();
    const {data: count} = useCountProductsInSelectionCriteria(criteria);

    const label =
        null === count || undefined === count
            ? translate('akeneo_catalogs.product_selection.count.error')
            : translate('akeneo_catalogs.product_selection.count.products', {count: count.toString()}, count);

    return (
        <Container>
            <Count>{label}</Count>
        </Container>
    );
};
