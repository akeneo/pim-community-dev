import React, {ReactElement} from 'react';
import {Tag} from 'akeneo-design-system';
import styled, {keyframes} from 'styled-components';
import {useTranslate} from '../../shared/translate';

const fadeInAnimation = keyframes`
 0% { opacity: 0; }
 100% { opacity: 1; }
`;

const BadgeContainer = styled.div`
    display: flex;
    height: 100%;
    align-items: center;
    margin: 0 20px;

    animation-name: ${fadeInAnimation};
    animation-duration: 500ms;
    animation-delay: 600ms;
    animation-iteration-count: 1;
    animation-fill-mode: forwards;

    opacity: 0;
`;

const DeveloperModeTag = (): ReactElement => {
    const translate = useTranslate();
    return (
        <BadgeContainer>
            <Tag tint='purple'>{translate('akeneo_connectivity.connection.developer_mode')}</Tag>
        </BadgeContainer>
    );
};

export {DeveloperModeTag};
