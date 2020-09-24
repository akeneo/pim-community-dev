import React, {FC, useCallback, useRef} from 'react';
import styled from 'styled-components';
import {MoveIcon} from '@akeneo-pim-community/shared';
import {MovePosition, TableCell, TableRow} from '../shared';
import {AttributeGroup} from '../../models';
import {useAttributeGroupLabel, useAttributeGroupsListState, useDragState} from '../../hooks';

type Props = {
    group: AttributeGroup;
    isSortable: boolean;
    index: number;
};

const Label = styled.span`
    width: 71px;
    height: 16px;
    color: ${({theme}) => theme.color.purple100};
    font-size: ${({theme}) => theme.fontSize.default};
    font-family: ${({theme}) => theme.font.default};
    font-weight: bold;
    font-style: italic;
`;

const AttributeGroupRow: FC<Props> = ({group, isSortable, index}) => {
    const {move, saveOrder, redirect} = useAttributeGroupsListState();
    const label = useAttributeGroupLabel(group);
    const {
        dragItem,
        initDragItem,
        handleDragEndCapture,
        handleDragStartCapture,
        handleDragEnterCapture,
        handleDragLeaveCapture,
        handleDragStart,
        handleDragOver,
        handleDrop,
        handleDragEnd,
        handleDragEnter,
        handleDragLeave
    } = useDragState();
    const rowRef = useRef(null);

    const handleRedirectToGroup = useCallback(() => {
        if (isSortable) {
            redirect(group);
        }
    }, [redirect, group, isSortable]);

    const handleRowDragStart = useCallback((event: React.DragEvent) => {
        handleDragStart(event, index, group, rowRef.current);
    }, [handleDragStart, index, group]);

    const handleRowDragOver = useCallback((event: React.DragEvent) => {
        handleDragOver(event, index, group, (dragItem, dropTarget) => {
            move(dragItem.data, group, MovePosition.Down);
        }, (dragItem, dropTarget) => {
            move(dragItem.data, group, MovePosition.Up);
        });
    }, [handleDragOver, index, group, dragItem]);

    const handleRowDragEnter = useCallback((event: React.DragEvent) => {
        handleDragEnter(event, (dragItem, dropTarget) => {});
    }, [handleDragEnter, index, group]);

    const handleRowDragLeave = useCallback((event: React.DragEvent) => {
        handleDragLeave(event, (dragItem, dropTarget) => {});
    }, [handleDragLeave]);

    const handleRowDrop = useCallback((event: React.DragEvent) => {
        handleDrop(event, () => {
            saveOrder();
        });
    }, [handleDrop, saveOrder]);

    const handleRowDragEnd = useCallback((event: React.DragEvent) => {
        handleDragEnd(event, () => {});
    }, [handleDragEnd, saveOrder]);

    const isDragged = isSortable && dragItem !== null && dragItem.data.code === group.code;

    return (
        <TableRow
            onClick={handleRedirectToGroup}
            ref={rowRef}
            isDragged={isDragged}
        >
            {isSortable && (
                <TableCell
                   width={40}
                   isDraggable={true}
                   onDragStart={handleRowDragStart}
                   onDragOver={handleRowDragOver}
                   onDragEnter={handleRowDragEnter}
                   onDragLeave={handleRowDragLeave}
                   onDrop={handleRowDrop}
                   onDragEnd={handleRowDragEnd}
                >
                    {/* @todo test with draggable child
                   onDragStartCapture={handleDragStartCapture}
                   onDragEndCapture={handleDragEndCapture}
                   onDragEnterCapture={handleDragEnterCapture}
                   onDragLeaveCapture={handleDragLeaveCapture}
                    */}
                    {/* @todo test with <div draggable={true}>...</div> */}
                    <MoveIcon />
                </TableCell>
            )}
            <TableCell>
                <Label>{label}</Label>
            </TableCell>
        </TableRow>
    );
};

export {AttributeGroupRow};