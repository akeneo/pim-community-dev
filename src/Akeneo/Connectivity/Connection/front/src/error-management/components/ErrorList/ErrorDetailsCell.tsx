import React, {FC} from 'react';
import {Flag} from '../../../common';
import TableCell from '../../../common/components/Table/TableCell';
import styled from '../../../common/styled-with-theme';
import {Translate} from '../../../shared/translate';
import {ConnectionErrorContent} from '../../model/ConnectionError';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorDetailsCell: FC<Props> = ({content}) => {
    return (
        <Container>
            {'string' === typeof content?.locale ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.locale' />
                        :{' '}
                    </DetailLabel>
                    <Flag locale={content.locale} /> {content.locale}
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
            {'string' === typeof content?.scope ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.channel' />
                        :{' '}
                    </DetailLabel>
                    {content.scope}
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
            {'string' === typeof content?.product?.family ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.family' />
                        :{' '}
                    </DetailLabel>
                    {content.product.family}
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
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
    white-space: nowrap;
`;

const DetailLabel = styled.span`
    font-weight: bold;
`;

export {ErrorDetailsCell};
