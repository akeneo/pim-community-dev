import React, {createContext, FC} from "react";
import {useInitialDragState} from "../../../hooks/useDragState";

type DragItem = {
    index: number;
    data: any;
};

interface DragCallbackFn {
    (dragItem: DragItem, target: Element): void;
}

type DragState = {
    isDragEnabled: boolean;
    dragItem: DragItem | null;
    initDragItem: (index: number, data: any) => void;
    resetDragItem: () => void;
    handleDragStart: (event: React.DragEvent, index: number, data: any, dragImage?: Element|null) => void;
    handleDragOver: (event: React.DragEvent, index: number, data: any, dragDownCallback: DragCallbackFn, dragUpCallback: DragCallbackFn) => void;
    handleDragEnter: (event: React.DragEvent, dragEnterCallback: DragCallbackFn) => void;
    handleDragLeave: (event: React.DragEvent, dragOverCallback: DragCallbackFn) => void;
    handleDrop: (event: React.DragEvent, dropCallback: DragCallbackFn) => void;
    handleDragEnd: (event: React.DragEvent, dropCallback: DragCallbackFn) => void;
    handleDragStartCapture: (event: React.DragEvent) => void;
    handleDragEndCapture: (event: React.DragEvent) => void;
    handleDragEnterCapture: (event: React.DragEvent) => void;
    handleDragLeaveCapture: (event: React.DragEvent) => void;
};

const DragStateContext = createContext<DragState>({
    isDragEnabled: false,
    dragItem: null,
    initDragItem: () => {},
    resetDragItem: () => {},
    handleDragStart: () => {},
    handleDragOver: () => {},
    handleDragEnter: () => {},
    handleDragLeave: () => {},
    handleDrop: () => {},
    handleDragEnd: () => {},
    handleDragStartCapture: () => {},
    handleDragEndCapture: () => {},
    handleDragEnterCapture: () => {},
    handleDragLeaveCapture: () => {},
});

type Props = {
    isEnabled: boolean;
};

const DragStateProvider: FC<Props> = ({children, isEnabled}) => {
    const state: DragState = useInitialDragState(isEnabled);

    return (
        <DragStateContext.Provider value={state}>{children}</DragStateContext.Provider>
    );
}

export {DragState, DragItem, DragCallbackFn, DragStateContext, DragStateProvider};