import React, {FC} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {getColor, getFontSize, ProductsIllustration} from 'akeneo-design-system';
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
    font-size: ${getFontSize('bigger')};
`;

const Link = styled.a`
    color: ${getColor('brand100')};
    text-decoration: underline ${getColor('brand100')};
    margin-top: 11px;
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
