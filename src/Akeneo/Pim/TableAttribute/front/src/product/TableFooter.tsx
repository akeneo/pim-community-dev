import React from 'react';
import {
  AkeneoThemedProps,
  ArrowLeftIcon,
  ArrowRightIcon,
  Dropdown, getColor,
  IconButton,
  SwitcherButton,
  useBooleanState
} from "akeneo-design-system";
import { TABLE_VALUE_ITEMS_PER_PAGE } from "./TableInputValue";
import styled from "styled-components";

const TableFooterContainer = styled.div`
  display: flex;  
  height: 44px;
  align-items: center;
`

const TableFooterElement = styled.div<{grow: boolean} & AkeneoThemedProps>`
  border-right: 1px solid ${getColor('grey', 100)};
  padding: 0 20px;
  height: 24px;
  line-height: 24px;
  text-align: right;
  ${({grow}) => grow ? 'flex-grow: 1': ''}
`

const IconContainer = styled(TableFooterContainer)`
  border-right: none;
`

type TableFooterProps = {
  itemsPerPage: number;
  currentPage: number;
  rowsCount: number;
  setCurrentPage: (page: number) => void;
  setItemsPerPage: (items: number) => void;
};

const TableFooter: React.FC<TableFooterProps> = ({
  itemsPerPage,
  currentPage,
  rowsCount,
  setCurrentPage,
  setItemsPerPage,
}) => {
  const [isItemsPerPageOpen, openItemsPerPage, closeItemsPerPage] = useBooleanState();

  const handleChangeItemsPerPage = (itemsPerPageChoice: number) => {
    setItemsPerPage(itemsPerPageChoice);
    setCurrentPage(0);
    closeItemsPerPage();
  }

  return <TableFooterContainer>
    <TableFooterElement>
      <Dropdown>
        <SwitcherButton label={'Items per page'} onClick={openItemsPerPage}>{itemsPerPage}</SwitcherButton>
        {isItemsPerPageOpen && <Dropdown.Overlay verticalPosition="down" onClose={close}>
          <Dropdown.Header>
            <Dropdown.Title>Items per page</Dropdown.Title>
          </Dropdown.Header>
          <Dropdown.ItemCollection>
            {TABLE_VALUE_ITEMS_PER_PAGE.map(itemsPerPageChoice => {
              return <Dropdown.Item
                key={itemsPerPageChoice}
                onClick={() => handleChangeItemsPerPage(itemsPerPageChoice)}
                isActive={itemsPerPage === itemsPerPageChoice}
              >{itemsPerPageChoice}</Dropdown.Item>
            })}
          </Dropdown.ItemCollection>
        </Dropdown.Overlay>}
      </Dropdown>
    </TableFooterElement>
    <TableFooterElement grow={true}>
      {Math.min(itemsPerPage * currentPage + 1, rowsCount)}-{Math.min(itemsPerPage * (currentPage + 1), rowsCount)} of {rowsCount} items
    </TableFooterElement>
    <TableFooterElement>
      Page {currentPage + 1} / {Math.ceil(rowsCount / itemsPerPage)}
    </TableFooterElement>
    <IconContainer>
      <IconButton
        ghost="borderless"
        level="tertiary"
        icon={<ArrowLeftIcon/>}
        title={'Gauche'}
        onClick={() => setCurrentPage(currentPage - 1)}
        disabled={currentPage <= 0}
      />
      <IconButton
        ghost="borderless"
        level="tertiary"
        icon={<ArrowRightIcon/>}
        title={'Droite'}
        onClick={() => setCurrentPage(currentPage + 1)}
        disabled={currentPage >= (Math.ceil(rowsCount / itemsPerPage) - 1)}
      />
    </IconContainer>
  </TableFooterContainer>
}

export {TableFooter};
