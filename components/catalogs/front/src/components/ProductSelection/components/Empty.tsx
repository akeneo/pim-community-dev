import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize, Link, ProductsIllustration} from 'akeneo-design-system';
import styled from 'styled-components';

const EmptyContainer = styled.div`
    text-align: center;
    margin: 60px auto;
`;

const Illustration = styled.div`
    vertical-align: middle;
`;

const Message = styled.div`
    color: ${getColor('grey140')};
    font-size: ${getFontSize('title')};
    margin: 11px 0 20px;
    line-height: 40px;
`;

const Empty: FC = () => {
    const translate = useTranslate();

    return (
        <EmptyContainer>
            <Illustration>
                <ProductsIllustration />
            </Illustration>
            <Message>{translate('akeneo_catalogs.product_selection.empty')}</Message>
            <Link href='#'>{translate('akeneo_catalogs.product_selection.more_information')}</Link>
        </EmptyContainer>
    );
};

export {Empty};
