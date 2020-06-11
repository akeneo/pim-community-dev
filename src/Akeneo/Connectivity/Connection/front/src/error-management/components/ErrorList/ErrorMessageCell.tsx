import React, {FC} from 'react';
import TableCell from '../../../common/components/Table/TableCell';
import styled from '../../../common/styled-with-theme';
import {DocumentationList} from '../Documentation/DocumentationList';
import {ConnectionErrorContent} from '../../model/ConnectionError';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageCell: FC<Props> = ({content}) => {
    // TGG DEBUG
    console.log(content);

    const ProductRow =
        'product' in content &&
        null !== content.product &&
        'undefined ' !== content.product &&
        'undefined ' !== content.product?.label ? (
            <ErrorContentRow>
                Product name <ProductName>{content.product?.label}</ProductName> with the ID{' '}
                <strong>{content.product?.id}</strong>
            </ErrorContentRow>
        ) : null;

    return (
        <Container>
            {ProductRow}
            {content.documentation !== undefined && <DocumentationList documentations={content.documentation} />}
        </Container>
    );
};

const Container = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
    white-space: pre-wrap;
`;

const ErrorContentRow = styled.tr`
    line-height: ${({theme}) => theme.fontSize.default};
`;

const ProductName = styled.span`
    color: ${({theme}) => theme.color.purple100};
    font-style: italic;
`;

export {ErrorMessageCell};
