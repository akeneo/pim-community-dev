import React, {ReactNode} from 'react';
import {Select} from '../../common';
import styled from '../../common/styled-with-theme';
import {Connection} from '../../model/connection';
import {useMediaUrlGenerator} from '../../settings/use-media-url-generator';

interface Props {
    connections: Connection[];
    onChange: (code?: string) => void;
    label: ReactNode;
}

export const ConnectionSelect = ({connections, onChange, label}: Props) => {
    const generate = useMediaUrlGenerator();

    const data = connections.reduce((data, connection) => {
        data[connection.code] = {
            label: connection.label,
            imageSrc: (connection.image && generate(connection.image, 'dropdown_select_picture')) || undefined,
        };
        return data;
    }, {} as {[code: string]: {label: string; imageSrc?: string}});

    return (
        <span>
            <Label>{label}</Label>
            <Select data={data} onChange={onChange} dropdownTitle={label} />
        </span>
    );
};

const Label = styled.span`
    color: ${({theme}) => theme.color.grey140};
    font-size: ${({theme}) => theme.fontSize.default};
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
