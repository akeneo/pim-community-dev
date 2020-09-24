import React, {createContext, PropsWithChildren} from "react";
import {useInitialDataGridState} from "../../../hooks";

enum MoveDirection {Up, Down}

type AfterMoveRowHandler<T> = (reorderedDataSource: T[]) => void;
type CompareRowDataHandler<T> = (source: T, target: T) => number;
type MoveRowHandler<T> = (source: T, target: T) => void;
type AfterDropRowHandler = () => void;
type StartMoveRowHandler<T> = (event: React.DragEvent, source: T, index: number, rowElement: Element | null) => void;
type MoveOverRowHandler<T> = (event: React.DragEvent, target: T, index: number) => void;
type MoveDropRowHandler = (event: React.DragEvent, handleDropRow: AfterDropRowHandler) => void;
type MoveEndRowHandler = (event: React.DragEvent) => void;

type DataGridState<T> = {
    isDraggable: boolean;
    dataSource: T[];
    isDragged: (data: T) => boolean;
    moveUp: MoveRowHandler<T>;
    moveDown: MoveRowHandler<T>
    moveStart: StartMoveRowHandler<T>;
    moveOver: MoveOverRowHandler<T>;
    moveDrop: MoveDropRowHandler;
    moveEnd: MoveEndRowHandler;
};

type Props<T> = {
    isDraggable: boolean;
    dataSource: T[];
    handleAfterMove: AfterMoveRowHandler<T>;
    compareData: CompareRowDataHandler<T>;
};

const DataGridStateContext = createContext<DataGridState<any>>({
    isDraggable: false,
    dataSource: [],
    isDragged: () => false,
    moveUp: () => {},
    moveDown: () => {},
    moveStart: () => {},
    moveOver: () => {},
    moveDrop: () => {},
    moveEnd: () => {},
});

const DataGridStateProvider = <T extends {}>({children, isDraggable, dataSource, handleAfterMove, compareData}: PropsWithChildren<Props<T>>) => {
    const state = useInitialDataGridState(isDraggable, dataSource, handleAfterMove, compareData);

    return (
        <DataGridStateContext.Provider value={state}>{children}</DataGridStateContext.Provider>
    )
}

export {
    MoveDirection,
    DataGridStateContext,
    DataGridState,
    DataGridStateProvider,
    AfterMoveRowHandler,
    AfterDropRowHandler,
    MoveRowHandler,
    StartMoveRowHandler,
    MoveOverRowHandler,
    MoveDropRowHandler,
    MoveEndRowHandler,
    CompareRowDataHandler,
}