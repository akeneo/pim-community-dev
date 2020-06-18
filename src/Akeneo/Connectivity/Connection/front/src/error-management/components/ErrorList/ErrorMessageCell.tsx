import React, {FC} from 'react';
import TableCell from '../../../common/components/Table/TableCell';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent, ErrorMessageDomainType, ErrorMessageViolationType} from '../../model/ConnectionError';
import {DocumentationList} from '../Documentation/DocumentationList';
import {ErrorMessageDomain} from './ErrorMessageDomain';
import {ErrorMessageUnformattedList} from './ErrorMessageUnformattedList';
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
            ) : ErrorMessageViolationType === content.type ? (
                <ErrorMessageViolation content={content} />
            ) : (
                <ErrorMessageUnformattedList content={content} />
            )}
            {content.documentation && <DocumentationList documentations={content.documentation} />}
        </Container>
    );
};

const Container = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
`;

export {ErrorMessageCell};
