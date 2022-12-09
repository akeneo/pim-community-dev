import React from 'react';
import {
  AkeneoThemedProps,
  ArrowLeftIcon,
  ArrowRightIcon,
  Dropdown,
  getColor,
  IconButton,
  SwitcherButton,
  useBooleanState,
} from 'akeneo-design-system';
import {TABLE_VALUE_ITEMS_PER_PAGE} from './TableInputValue';
import styled from 'styled-components';
import {useTranslate} from '@akeneo-pim-community/shared';

const TableFooterContainer = styled.div`
  display: flex;
  height: 44px;
  align-items: center;
`;

const TableFooterElement = styled.div<{grow: boolean} & AkeneoThemedProps>`
  border-right: 1px solid ${getColor('grey', 100)};
  padding: 0 20px;
  height: 24px;
  line-height: 24px;
  text-align: right;
  ${({grow}) => (grow ? 'flex-grow: 1' : '')}
`;

const IconContainer = styled(TableFooterContainer)`
  border-right: none;
`;

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
  const translate = useTranslate();

  const handleChangeItemsPerPage = (itemsPerPageChoice: number) => {
    setItemsPerPage(itemsPerPageChoice);
    setCurrentPage(0);
    closeItemsPerPage();
  };

  return (
    <TableFooterContainer>
      <TableFooterElement grow={false}>
        <Dropdown>
          <SwitcherButton
            label={translate('pim_table_attribute.form.product.items_per_page')}
            onClick={openItemsPerPage}
          >
            {itemsPerPage}
          </SwitcherButton>
          {isItemsPerPageOpen && (
            <Dropdown.Overlay onClose={closeItemsPerPage}>
              <Dropdown.Header>
                <Dropdown.Title>{translate('pim_table_attribute.form.product.items_per_page')}</Dropdown.Title>
              </Dropdown.Header>
              <Dropdown.ItemCollection>
                {TABLE_VALUE_ITEMS_PER_PAGE.map(itemsPerPageChoice => {
                  return (
                    <Dropdown.Item
                      key={itemsPerPageChoice}
                      onClick={() => handleChangeItemsPerPage(itemsPerPageChoice)}
                      isActive={itemsPerPage === itemsPerPageChoice}
                    >
                      {itemsPerPageChoice}
                    </Dropdown.Item>
                  );
                })}
              </Dropdown.ItemCollection>
            </Dropdown.Overlay>
          )}
        </Dropdown>
      </TableFooterElement>
      <TableFooterElement grow={true}>
        {translate('pim_table_attribute.form.product.current_items', {
          minItem: Math.min(itemsPerPage * currentPage + 1, rowsCount),
          maxItem: Math.min(itemsPerPage * (currentPage + 1), rowsCount),
          itemCount: rowsCount,
        })}
      </TableFooterElement>
      <TableFooterElement grow={false}>
        {translate('pim_table_attribute.form.product.current_page', {
          currentPage: currentPage + 1,
          pageCount: Math.ceil(rowsCount / itemsPerPage),
        })}
      </TableFooterElement>
      <IconContainer>
        <IconButton
          ghost='borderless'
          level='tertiary'
          icon={<ArrowLeftIcon />}
          title={translate('pim_common.previous')}
          onClick={() => setCurrentPage(currentPage - 1)}
          disabled={currentPage <= 0}
        />
        <IconButton
          ghost='borderless'
          level='tertiary'
          icon={<ArrowRightIcon />}
          title={translate('pim_common.next')}
          onClick={() => setCurrentPage(currentPage + 1)}
          disabled={currentPage >= Math.ceil(rowsCount / itemsPerPage) - 1}
        />
      </IconContainer>
    </TableFooterContainer>
  );
};

export {TableFooter};
