import React, {FC, useContext} from 'react';
import {css} from 'styled-components';
import {Flag} from '../../../common';
import styled from '../../../common/styled-with-theme';
import {Translate} from '../../../shared/translate';
import {ConnectionErrorContent} from '../../model/ConnectionError';
import {FamilyContext} from '../../../shared/family/family-context';
import {ChannelContext} from '../../../shared/channel/channel-context';

type Props = {
    content: ConnectionErrorContent;
};

const ErrorDetailsCell: FC<Props> = ({content}) => {
    const families = useContext(FamilyContext);
    const channels = useContext(ChannelContext);

    let familyLabel = content.product?.family;
    if (undefined !== families && 'string' === typeof content.product?.family) {
        const foundFamilyLabel = families.find(fam => fam.code === content.product?.family);
        if (undefined !== foundFamilyLabel) {
            familyLabel = foundFamilyLabel.label;
        }
    }

    let channelLabel = content.scope;
    if (undefined !== channels && 'string' === typeof content.scope) {
        const foundChannelLabel = channels.find(c => c.code === content.scope);
        if (undefined !== foundChannelLabel) {
            channelLabel = foundChannelLabel.label;
        }
    }

    return (
        <Container>
            {'string' === typeof content.locale ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.locale' />
                        :{' '}
                    </DetailLabel>
                    <Flag locale={content.locale} />
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
            {'string' === typeof content.scope ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.channel' />
                        :{' '}
                    </DetailLabel>
                    {channelLabel}
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
            {'string' === typeof content.product?.family ? (
                <DetailRow>
                    <DetailLabel>
                        <Translate id='akeneo_connectivity.connection.error_management.connection_monitoring.error_list.details_column.family' />
                        :{' '}
                    </DetailLabel>
                    {familyLabel}
                </DetailRow>
            ) : (
                <DetailRow></DetailRow>
            )}
        </Container>
    );
};

const Container = styled.td<{collapsing?: boolean}>`
    border-bottom: 1px solid ${({theme}) => theme.color.grey60};
    color: ${({theme}) => theme.color.grey140};
    padding: 15px 20px;
    white-space: pre-wrap;

    ${({collapsing}) =>
        collapsing &&
        css`
            width: 1px;
            white-space: nowrap;
        `}
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
