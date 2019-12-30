import React, {FC, ReactNode, useEffect, useRef} from 'react';
import styled from 'styled-components';

type Props = {
    children: ReactNode;
    onClose: () => void;
};

export const Dropdown: FC<Props> = ({children, onClose}: Props) => {
    const ref = useRef<HTMLDivElement>(null);

    const handleMouseDown = (event: MouseEvent) => {
        if (null === ref.current || null === event.target) {
            return;
        }
        if (true === ref.current.contains(event.target as any)) {
            return;
        }
        onClose();
    };

    const handleKeyDown = (event: KeyboardEvent) => event.key === 'Escape' && onClose();

    useEffect(() => {
        document.addEventListener('mousedown', handleMouseDown);
        document.addEventListener('keydown', handleKeyDown);
        return () => {
            document.removeEventListener('mousedown', handleMouseDown);
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, [onClose, handleKeyDown]);

    return <Container ref={ref}>{children}</Container>;
};

const Container = styled.div`
    background-color: white;
    box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
    width: 240px;
    padding: 10px 20px;
    position: absolute;
    right: 0;
    z-index: 10000;
`;
