import React, {PropsWithChildren, useRef} from 'react';
import {TableCell, TableRow} from "../layouts/tables";
import {useDataGridState} from "../../../hooks";
import {AfterDropRowHandler} from "../providers";
import {DraggableRowWrapper} from "./DraggableRowWrapper";

type Props<T> = {
    data: T;
    index: number;
    handleClick?: React.MouseEventHandler;
    handleDrop?: AfterDropRowHandler;
};

const Row = <T extends {}>({children, index, data, handleDrop, handleClick}: PropsWithChildren<Props<T>>) => {
    const {isDragged} = useDataGridState();
    const rowRef = useRef(null);

    return (
        <TableRow
            onClick={handleClick}
            ref={rowRef}
            isDragged={isDragged(data)}
        >
            <DraggableRowWrapper index={index} data={data} rowRef={rowRef} handleDrop={(handleDrop) ? handleDrop : () => {}}>
                {React.Children.map(children, (element) => (
                    <TableCell>{element}</TableCell>
                ))}
            </DraggableRowWrapper>
        </TableRow>
    );
};

export {Row}
