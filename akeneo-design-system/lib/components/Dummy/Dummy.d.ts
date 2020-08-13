import { ReactNode } from 'react';
export declare type Type = 'primary' | 'secondary';
declare type DummyProps = {
    /**
     * Defines the type of the Dummy component
     */
    type?: Type;
    /**
     * Defines the size of the Dummy component, in pixels
     */
    size?: number;
    /**
     * The handler called when clicking the component
     */
    onClick?: () => void;
    children?: ReactNode;
};
/**
 * This is a nice Dummy component to bootstrap Storybook
 */
declare const Dummy: ({ size, type, onClick, children }: DummyProps) => JSX.Element;
export { Dummy };
export type { DummyProps };
