import React, {ReactElement, ReactNode} from 'react';
import {IconProps, MessageBar, MessageBarLevel, uuid} from 'akeneo-design-system';
import styled from 'styled-components';

const Container = styled.div`
    position: absolute;
    right: 40px;
    bottom: 40px;
    display: flex;
    flex-direction: column;
    gap: 20px;
`;

type Toast = {
    title: string;
    level?: MessageBarLevel;
    icon?: ReactElement<IconProps>;
    message?: ReactNode;
};

type Props = {
    toasts: Toast[];
};

const Toaster = ({toasts}: Props) => {
    return (
        <Container>
            {toasts.map(toast => (
                <MessageBar
                    icon={toast.icon}
                    key={uuid()}
                    level={toast.level}
                    title={toast.title}
                    dismissTitle=""
                    onClose={function doNothing() {}}
                >
                    {toast.message}
                </MessageBar>
            ))}
        </Container>
    );
};

export {Toaster};
export type {Toast};
