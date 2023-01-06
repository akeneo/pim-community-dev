import React, {FC} from 'react';
import {ProductMapping as ProductMappingType} from '../../ProductMapping/models/ProductMapping';
import {usePreviewMappedProduct} from '../hooks/usePreviewMappedProduct';
import styled from 'styled-components';

const StyledPre = styled.pre`
    outline: 1px solid #ccc;
    padding: 5px;
    margin: 0;
    width: 100%;

    .string {
        color: green;
    }
    .number {
        color: darkorange;
    }
    .boolean {
        color: blue;
    }
    .null {
        color: magenta;
    }
    .key {
        color: red;
    }
`;

const syntaxHighlight = function (json: string): string {
    json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return json.replace(
        /("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g,
        function (match: string) {
        let className = 'number';
        if (/^"/.test(match)) {
            if (/:$/.test(match)) {
                className = 'key';
            } else {
                className = 'string';
            }
        } else if (/true|false/.test(match)) {
            className = 'boolean';
        } else if (/null/.test(match)) {
            className = 'null';
        }
        return '<span class="' + className + '">' + match + '</span>';
    });
}


type Props = {
    catalogId: string,
    productId: string,
    productMapping: ProductMappingType,
};
export const PreviewContainer: FC<Props> = ({catalogId, productId, productMapping}) => {
    const previewMappedProduct = usePreviewMappedProduct(catalogId, productId, productMapping);

    if (previewMappedProduct.isLoading) {
        return <pre>Loading...</pre>;
    }

    if (previewMappedProduct.isError || undefined === previewMappedProduct.data) {
        throw new Error(previewMappedProduct.error?.message || undefined);
    }

    if (null === previewMappedProduct.data) {
        return <pre>No mapped data.</pre>;
    }

    return <StyledPre dangerouslySetInnerHTML={{__html: syntaxHighlight(
        JSON.stringify(previewMappedProduct.data, null, 2))}} />;
};

