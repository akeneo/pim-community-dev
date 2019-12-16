import React, {ReactNode} from 'react';
import styled from 'styled-components';
import {App as AppInterface} from '../../../domain/apps/app.interface';
import {Section} from '../../common';
import {Translate} from '../../shared/translate';
import {App} from './App';

const AppCount = styled.div`
    color: #9452ba;
    line-height: 44px;
`;

const Grid = styled.div`
    margin: 10px 0;
    display: grid;
    grid-gap: 20px;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
`;

interface Props {
    apps: AppInterface[];
    title: ReactNode;
}

export const AppGrid = ({apps, title}: Props) => (
    <>
        <Section title={title}>
            <AppCount>
                <Translate id='pim_apps.app_count' count={apps.length} placeholders={{count: apps.length}} />
            </AppCount>
        </Section>
        <Grid>
            {apps.map((app: AppInterface) => (
                <App code={app.code} label={app.label} image={app.image} key={app.code} />
            ))}
        </Grid>
    </>
);
