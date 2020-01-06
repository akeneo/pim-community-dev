import React from 'react';
import styled from 'styled-components';
import {PageError} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {Translate} from '../../shared/translate';

export const NoConnection = ({onCreate}: {onCreate: () => void}) => (
    <PageError title={<Translate id='akeneo_connectivity.connection.no_connection.title' />}>
        <Translate id='akeneo_connectivity.connection.no_connection.message' />
        &nbsp;
        <Link onClick={onCreate}>
            <Translate id='akeneo_connectivity.connection.no_connection.message_link' />
        </Link>
    </PageError>
);

const Link = styled.a`
    color: #9452ba;
    cursor: pointer;
    text-decoration: underline ${({theme}: PropsWithTheme) => theme.color.purple100};
`;
