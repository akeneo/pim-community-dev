import React, {FC, useContext} from 'react';
import imgUrl from '../../../common/assets/illustrations/NewAPI.svg';
import styled from '../../../common/styled-with-theme';
import {useMediaUrlGenerator} from '../../../settings/use-media-url-generator';
import {RouterContext, useRoute} from '../../../shared/router';
import {Translate} from '../../../shared/translate';

type Props = {
    code: string;
    label: string;
    image: string | null;
    errorCount: number;
};

export const BusinessErrorCard: FC<Props> = ({code, label, image, errorCount}) => {
    const generateMediaUrl = useMediaUrlGenerator();
    const {redirect} = useContext(RouterContext);

    const routeToConnectionMonitoring = useRoute(
        'akeneo_connectivity_connection_error_management_connection_monitoring',
        {code}
    );

    const connectionImageUrl = null === image ? imgUrl : generateMediaUrl(image, 'thumbnail');

    return (
        <Card onClick={() => redirect(routeToConnectionMonitoring)}>
            <CardImage src={connectionImageUrl} alt={label} />
            <CardContent>
                <ConnectionLabel title={label}>{label}</ConnectionLabel>
                <ErrorCountLabel>
                    <ErrorCount>{errorCount}</ErrorCount>
                    &nbsp;
                    <Translate id='akeneo_connectivity.connection.dashboard.error_management.widget.business_errors' />
                </ErrorCountLabel>
                <OverTheLastSevenDays>
                    <Translate id='akeneo_connectivity.connection.dashboard.error_management.widget.over_the_last_seven_days' />
                </OverTheLastSevenDays>
            </CardContent>
        </Card>
    );
};

const Card = styled.div`
    cursor: pointer;
    display: flex;
`;

const CardImage = styled.img`
    border: 1px solid ${({theme}) => theme.color.grey100};
    box-sizing: border-box;
    display: block;
    flex-shrink: 0;
    height: 80px;
    object-fit: cover;
    width: 80px;
`;

const CardContent = styled.div`
    margin-left: 20px;
    margin-top: 5px;
    min-width: 0;
`;

const ConnectionLabel = styled.div`
    color: ${({theme}) => theme.color.grey140};
    font-size: ${({theme}) => theme.fontSize.big};
    font-weight: bold;
    margin-bottom: 10px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
`;

const ErrorCountLabel = styled.div`
    color: ${({theme}) => theme.color.grey140};
`;

const ErrorCount = styled.span`
    color: ${({theme}) => theme.color.red100};
    font-size: ${({theme}) => theme.fontSize.metricsBig};
    font-weight: bold;
    line-height: 1.2em;
`;

const OverTheLastSevenDays = styled.div`
    font-size: ${({theme}) => theme.fontSize.small};
    line-height: 1.2em;
`;
