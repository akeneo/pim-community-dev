import React, {PropsWithChildren, RefObject, useRef} from 'react';
import {TableCell, TableRow} from "../layouts/tables";
import {MoveIcon} from "@akeneo-pim-community/shared/src";
import {useDataGridState} from "../../../hooks";
import {AfterDropRowHandler} from "../providers";

type Props<T> = {
    data: T;
    index: number;
    handleClick?: React.MouseEventHandler;
    handleDrop?: AfterDropRowHandler;
};

type DraggableProps<T> = {
    data: T;
    index: number;
    handleDrop?: AfterDropRowHandler;
    rowRef: RefObject<HTMLElement>;
};

const DraggableRow  = <T extends {}>({children, index, data, rowRef, handleDrop}: PropsWithChildren<DraggableProps<T>>) => {
    const {isDraggable, moveOver, moveStart, moveDrop, moveEnd} = useDataGridState();

    return (
        <>
            {isDraggable && (
                <TableCell
                    width={40}
                    isDraggable={true}
                    onDragStart={(event) => {
                        moveStart(event, data, index, rowRef.current);
                    }}
                    onDragOver={(event) => {
                        moveOver(event, data, index);
                    }}
                    onDrop={(event) => {
                        const afterMoveDropHandler = (!handleDrop) ? () => {} : handleDrop;

                        moveDrop(event, afterMoveDropHandler);
                    }}
                    onDragEnd={(event) => {
                        moveEnd(event);
                    }}
                >
                    {/* @todo test with <div draggable={true}>...</div> */}
                    <MoveIcon />
                </TableCell>
            )}
            {children}
        </>
    );
}

const Row = <T extends {}>({children, index, data, handleDrop, handleClick}: PropsWithChildren<Props<T>>) => {
    const {isDragged} = useDataGridState();
    const rowRef = useRef(null);

    return (
        <TableRow
            onClick={handleClick}
            ref={rowRef}
            isDragged={isDragged(data)}
        >
            <DraggableRow index={index} data={data} rowRef={rowRef} handleDrop={handleDrop}>
                {React.Children.map(children, (element) => (
                    <TableCell>{element}</TableCell>
                ))}
            </DraggableRow>
        </TableRow>
    );
};

export {Row}