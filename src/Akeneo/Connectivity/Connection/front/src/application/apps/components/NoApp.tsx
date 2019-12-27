import React from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {Translate} from '../../shared/translate';

export const NoApp = ({onCreate}: {onCreate: () => void}) => (
    <PageError title={<Translate id='pim_apps.no_app.title' />}>
        <Translate id='pim_apps.no_app.message' />
        &nbsp;
        <Link onClick={onCreate}>
            <Translate id='pim_apps.no_app.message_link' />
        </Link>
    </PageError>
);

const Link = styled.a`
    color: #9452ba;
    cursor: pointer;
    text-decoration: underline ${({theme}: PropsWithTheme) => theme.color.purple100};
`;
