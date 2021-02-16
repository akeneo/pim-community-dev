import React from 'react';
import styled from '../../../common/styled-with-theme';
import {Translate} from '../../../shared/translate';
import {Loading} from '../../../common';
import {useDashboardState} from '../../dashboard-context';
import {useBusinessErrorCountPerConnection} from '../../hooks/api/use-business-error-count-per-connection';
import {BusinessErrorCard} from './BusinessErrorCard';
import {SectionTitle} from 'akeneo-design-system';

const Grid = styled.div`
    margin: 20px 0;
    display: grid;
    grid-gap: 30px;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
`;

export const BusinessErrorCountWidget = () => {
    const {connections} = useDashboardState();
    const {loading, errorCountPerConnection} = useBusinessErrorCountPerConnection();

    if (loading) {
        return <Loading />;
    }

    if (errorCountPerConnection.length === 0) {
        return <></>;
    }

    return (
        <>
            <SectionTitle>
                <SectionTitle.Title>
                    <Translate id='akeneo_connectivity.connection.dashboard.error_management.widget.title' />
                </SectionTitle.Title>
            </SectionTitle>
            <Grid>
                {errorCountPerConnection.map(({errorCount, connectionCode}) => {
                    const connection = connections[connectionCode];
                    if (connection === undefined) {
                        return;
                    }
                    return (
                        <BusinessErrorCard
                            key={connectionCode}
                            code={connection.code}
                            label={connection.label}
                            image={connection.image}
                            errorCount={errorCount}
                        />
                    );
                })}
            </Grid>
        </>
    );
};
