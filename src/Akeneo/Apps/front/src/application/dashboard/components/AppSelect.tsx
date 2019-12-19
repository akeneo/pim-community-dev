import React from 'react';
import styled from 'styled-components';
import {App} from '../../../domain/apps/app.interface';
import {useMediaUrlGenerator} from '../../apps/use-media-url-generator';
import {Select} from '../../common';
import {PropsWithTheme} from '../../common/theme';
import {Translate} from '../../shared/translate';

interface Props {
    apps: App[];
    code: string;
    onChange: (code?: string) => void;
}

export const AppSelect = ({apps, onChange}: Props) => {
    const generate = useMediaUrlGenerator();

    const data = apps.reduce((data, app) => {
        data[app.code] = {label: app.label, imageSrc: (app.image && generate(app.image)) || undefined};
        return data;
    }, {} as {[code: string]: {label: string; imageSrc?: string}});

    return (
        <>
            <Label>
                <Translate id='akeneo_apps.dashboard.app_selector.title' />
            </Label>
            <Select
                data={data}
                onChange={onChange}
                dropdownTitle={<Translate id='akeneo_apps.dashboard.app_selector.title' />}
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
