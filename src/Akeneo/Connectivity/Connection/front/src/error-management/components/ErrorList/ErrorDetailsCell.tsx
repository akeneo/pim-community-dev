import React, {FC} from 'react';
import TableCell from '../../../common/components/Table/TableCell';
import styled from '../../../common/styled-with-theme';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {Flag} from '../../../common/components/Flag';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorDetailsCell: FC<Props> = ({content}) => {
    const localeRow =
        'locale' in content && null !== content.locale && 'string' === typeof content.locale ? (
            <DetailRow>
                <DetailLabel>Locale: </DetailLabel>
                <DateContent>
                    <Flag locale={content.locale} /> {content.locale}
                </DateContent>
            </DetailRow>
        ) : (
            <DetailRow></DetailRow>
        );

    const channelRow =
        'scope' in content && null !== content.scope ? (
            <DetailRow>
                <DetailLabel>Channel: </DetailLabel>
                <DateContent>{content.scope}</DateContent>
            </DetailRow>
        ) : (
            <DetailRow></DetailRow>
        );

    const familyRow =
        'product' in content &&
        null !== content.product &&
        'undefined' !== typeof content.product &&
        'family' in content.product &&
        null !== content.product.family ? (
            <DetailRow>
                <DetailLabel>Family: </DetailLabel>
                <DateContent>{content.product.family}</DateContent>
            </DetailRow>
        ) : (
            <DetailRow></DetailRow>
        );

    return (
        <Container>
            {localeRow}
            {channelRow}
            {familyRow}
        </Container>
    );
};

const Container = styled(TableCell)`
    color: ${({theme}) => theme.color.grey140};
    white-space: pre-wrap;
`;

const DetailRow = styled.div`
    line-height: ${({theme}) => theme.fontSize.default};
    color: ${({theme}) => theme.color.grey140};
    padding: 5px;
`;

const DetailLabel = styled.span`
    font-weight: bold;
`;

const DateContent = styled.span``;

/*
const DetailFlag = styled.span`
`;
*/

export {ErrorDetailsCell};
