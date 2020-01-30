import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {Section} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {Connection as ConnectionModel} from '../../model/connection';
import {Translate} from '../../shared/translate';
import {Connection} from './Connection';

const Count = styled.div`
    color: ${({theme}: PropsWithTheme) => theme.color.purple100};
    line-height: 44px;
`;

const Grid = styled.div`
    margin: 10px 0;
    display: grid;
    grid-gap: 20px;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
`;

type Props = {
    connections: ConnectionModel[];
    title: ReactNode;
};

export const ConnectionGrid = ({connections, title}: Props) => (
    <>
        <Section title={title}>
            <Count>
                <Translate
                    id='akeneo_connectivity.connection.connection_count'
                    count={connections.length}
                    placeholders={{count: connections.length.toString()}}
                />
            </Count>
        </Section>
        <Grid>
            {connections.map(connection => (
                <Connection
                    key={connection.code}
                    code={connection.code}
                    label={connection.label}
                    image={connection.image}
                />
            ))}
        </Grid>
    </>
);
