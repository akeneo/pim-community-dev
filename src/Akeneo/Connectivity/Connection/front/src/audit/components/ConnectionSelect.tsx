import React from 'react';
import styled from 'styled-components';
import {Select} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {Connection} from '../../model/connection';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';
import {Translate} from '../../shared/translate';

interface Props {
    connections: Connection[];
    code: string;
    onChange: (code?: string) => void;
}

export const ConnectionSelect = ({connections, onChange}: Props) => {
    const generate = useMediaUrlGenerator();

    const data = connections.reduce((data, connection) => {
        data[connection.code] = {
            label: connection.label,
            imageSrc: (connection.image && generate(connection.image, 'dropdown_select_picture')) || undefined,
        };
        return data;
    }, {} as {[code: string]: {label: string; imageSrc?: string}});

    return (
        <>
            <Label>
                <Translate id='akeneo_connectivity.connection.dashboard.connection_selector.title' />
            </Label>
            <Select
                data={data}
                onChange={onChange}
                dropdownTitle={<Translate id='akeneo_connectivity.connection.dashboard.connection_selector.title' />}
            />
        </>
    );
};

const Label = styled.span`
    color: ${({theme}: PropsWithTheme) => theme.color.grey140};
    font-size: ${({theme}: PropsWithTheme) => theme.fontSize.default};
    height: 44px;
    line-height: 44px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    ::after {
        content: ':';
        padding-right: 1ch;
    }
`;
