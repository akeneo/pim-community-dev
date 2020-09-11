import React, {ReactNode} from 'react';
import styled, {css} from "styled-components";
import {AkeneoThemedProps} from '../../theme';
import {InfoIcon, WarningIcon} from '../../icons';

const getColorStyle = ({level, theme}: { level: Level } & AkeneoThemedProps) => {
    switch (level) {
        case 'info':
            return css`
                background: ${theme.color.grey120}
                color: ${theme.color.grey120}
            `;
        case 'error':
            return css`
                background: ${theme.color.red120}
                color: ${theme.color.red100}
            `;
        case 'warning':
        default:
            return css`
                background: ${theme.color.yellow120}
                color: ${theme.color.yellow120}
            `;
    }
}

const Container = styled.div<{type: string; level: Level} & AkeneoThemedProps>`
  align-items: center;
  display: flex;
  font-weight: 600;
  margin-bottom: 1px;
  padding-right: 15px;
  
  ${getColorStyle}
  
  ${(props: any) => props.type === 'big' && css`font-size: big;`}
`;

const IconContainer = styled.span<{level: string} & AkeneoThemedProps>`
  padding: 12px;
  position: relative;
  display: flex;
  margin: 0 15px 0 0;

  &:after {

    content: '';
    display: block;
    height: 24px;
    margin-top: -12px;
    position: absolute;
    right: 0;
    top: 50%;
    width: 1px;
  }
`

const getIcon = (level: Level): JSX.Element => {
    switch (level) {
        case 'info':
            return <InfoIcon color={'blue120'} />;
        case 'error':
            return <WarningIcon color={'red120'} />;
        case 'warning':
        default:
            return <WarningIcon color={'yellow120'} />;
    }
};

type Level = 'info' | 'warning' | 'error';
type IconProps = {
    level: Level;
    icon?: ReactNode;
}
const Icon = ({level, icon}: IconProps) => (
  <IconContainer level={level}>{icon || getIcon(level)}</IconContainer>
);

type HelperType = 'big' | 'small' | 'inline';
type HelperProps = {
    type: HelperType;
    children: ReactNode;
    title?: string;
} & IconProps;
const Helper = ({type, level, title, children, icon}: HelperProps) => {
    if('big' === type && undefined === title) {
       throw new Error('A big helper should have a title. None given.')
    }
    if ('big' !== type && title !== undefined) {
        throw new Error('A small or inline helper cannot have title.')
    }

    return <Container type={type} level={level}>
        <Icon level={level} icon={icon} />
        {title && <div>{title}</div>}
        {children}
    </Container>;
}

export {Helper};
