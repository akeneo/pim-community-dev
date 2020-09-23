import React, {FC, ComponentType} from 'react';
import {DragStateProvider} from "../providers";

const withDragState = <P extends object>(WrappedComponent: ComponentType<P>): FC<P> => {
    return (props) => {
        return (
            <DragStateProvider isEnabled={true}>
                <WrappedComponent {...props} />
            </DragStateProvider>
        );
    }
};

export {withDragState};