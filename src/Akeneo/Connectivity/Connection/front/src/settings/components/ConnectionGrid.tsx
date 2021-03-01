import React, {ReactNode} from 'react';
import {HelperLink, SmallHelper} from '../../common';
import styled from '../../common/styled-with-theme';
import {Connection as ConnectionModel} from '../../model/connection';
import {WrongCredentialsCombinations} from '../../model/wrong-credentials-combinations';
import {Translate} from '../../shared/translate';
import {Connection} from './Connection';
import {SectionTitle} from 'akeneo-design-system';

const Grid = styled.div`
    margin: 10px 0;
    display: grid;
    grid-gap: 20px;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
`;

type Props = {
    connections: ConnectionModel[];
    wrongCredentialsCombinations: WrongCredentialsCombinations;
    title: ReactNode;
};

export const ConnectionGrid = ({connections, title, wrongCredentialsCombinations}: Props) => (
    <>
        <SectionTitle>
            <SectionTitle.Title>{title}</SectionTitle.Title>
            <SectionTitle.Spacer />
            <SectionTitle.Information>
                <Translate
                    id='akeneo_connectivity.connection.connection_count'
                    count={connections.length}
                    placeholders={{count: connections.length.toString()}}
                />
            </SectionTitle.Information>
        </SectionTitle>
        {0 !== Object.entries(wrongCredentialsCombinations).length && (
            <SmallHelper>
                <Translate id='akeneo_connectivity.connection.grid.wrong_credentials_combination_helper' />
                <div>
                    <HelperLink
                        href='https://help.akeneo.com/pim/serenity/articles/manage-your-connections.html#why-should-you-use-the-connection-username'
                        target='_blank'
                        rel='noopener noreferrer'
                    >
                        <Translate id='akeneo_connectivity.connection.edit_connection.credentials.wrong_credentials_combinations.link_list' />
                    </HelperLink>
                </div>
            </SmallHelper>
        )}
        <Grid>
            {connections.map(connection => (
                <Connection
                    key={connection.code}
                    code={connection.code}
                    label={connection.label}
                    image={connection.image}
                    hasWrongCombination={undefined !== wrongCredentialsCombinations[connection.code]}
                />
            ))}
        </Grid>
    </>
);
