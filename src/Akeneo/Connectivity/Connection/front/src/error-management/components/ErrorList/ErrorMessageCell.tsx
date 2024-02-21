import React, {FC} from 'react';
import {css} from 'styled-components';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent, ErrorMessageDomainType} from '../../model/ConnectionError';
import {DocumentationList} from '../Documentation/DocumentationList';
import {ErrorMessageDomain} from './ErrorMessageDomain';
import {ErrorMessageViolation} from './ErrorMessageViolation';
import {ErrorProductInformation} from './ErrorProductInformation';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorMessageCell: FC<Props> = ({content}) => {
    return (
        <Container>
            {content?.product && <ErrorProductInformation product={content.product} />}
            {ErrorMessageDomainType === content.type ? (
                <ErrorMessageDomain content={content} />
            ) : (
                <ErrorMessageViolation content={content} />
            )}
            {content.documentation && <DocumentationList documentations={content.documentation} />}
        </Container>
    );
};

const Container = styled.td<{collapsing?: boolean}>`
    border-bottom: 1px solid ${({theme}) => theme.color.grey60};
    color: ${({theme}) => theme.color.grey140};
    padding: 15px 20px;

    ${({collapsing}) =>
        collapsing &&
        css`
            width: 1px;
            white-space: nowrap;
        `}
`;

export {ErrorMessageCell};
