import React, {isValidElement, ReactNode, SyntheticEvent} from 'react';
import styled, {css} from 'styled-components';
import {AkeneoThemedProps, Badge, getColor, getFontSize} from 'akeneo-design-system';

type BadgeButtonProps = {
    onClick?: (event: SyntheticEvent) => void;
    isActive?: boolean;
    children?: ReactNode;
};

const BadgeButton = ({onClick, isActive = false, children}: BadgeButtonProps) => {
    const buttonLabel = React.Children.toArray(children).find(child => 'string' === typeof child);
    const badge = React.Children.toArray(children).find(child => isValidElement(child) && child.type === Badge);

    return (
        <Container isActive={isActive} onClick={onClick} hasBadge={badge !== undefined}>
            <Label>{buttonLabel}</Label>
            {badge}
        </Container>
    );
};

const activeDisplay = css`
    border-color: ${getColor('brand20')};
    background-color: ${getColor('brand20')};
    color: ${getColor('brand120')};
`;

const Container = styled.div<BadgeButtonProps & AkeneoThemedProps & {hasBadge: boolean}>`
    display: inline-flex;
    align-items: center;
    max-width: 100%;
    gap: 10px;
    font-weight: 400;
    font-size: ${getFontSize('big')} !important;
    color: ${getColor('grey120')};
    text-transform: capitalize;
    text-decoration: none;
    border-width: 1px;
    border-radius: 4px;
    border-style: solid;
    border-color: ${getColor('grey100')};
    background-color: transparent;
    padding: 6px 10px;
    cursor: pointer;
    ${({hasBadge}) =>
        hasBadge &&
        css`
            padding-right: 6px;
        `}

    ${({isActive}) =>
        isActive &&
        css`
            ${activeDisplay}
        `}

  &:hover {
        ${activeDisplay}
    }
`;

const Label = styled.div`
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
`;

export {BadgeButton};
