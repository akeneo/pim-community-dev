import React from 'react';
import {Section} from '../../../common';
import styled from '../../../common/styled-with-theme';
import {Translate} from '../../../shared/translate';
import {useBusinessErrorCountPerConnection} from '../../api-hooks/use-business-error-count-per-connection';
import {useDashboardState} from '../../dashboard-context';
import {BusinessErrorCard} from './BusinessErrorCard';

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
        return <>Loading...</>; // TODO Loading spinner
    }

    if (errorCountPerConnection.length === 0) {
        return <></>;
    }

    return (
        <>
            <Section
                title={<Translate id='akeneo_connectivity.connection.dashboard.error_management.widget.title' />}
            ></Section>
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
