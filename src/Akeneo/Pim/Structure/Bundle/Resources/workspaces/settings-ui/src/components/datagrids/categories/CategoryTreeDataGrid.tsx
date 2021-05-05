import React, {FC, useCallback, useEffect, useState} from 'react';
import {Button, Search, Table, useBooleanState} from 'akeneo-design-system';
import {
  NotificationLevel,
  useDebounceCallback,
  useNotify,
  useRouter,
  useSecurity,
  useTranslate,
} from '@akeneo-pim-community/shared';
import {CategoryTree} from '../../../models';
import styled from 'styled-components';
import {NoResults} from '../../shared';
import {DeleteCategoryModal} from './DeleteCategoryModal';
import {deleteCategory} from '../../../infrastructure/removers';
import {useCountCategoryTreesChildren} from '../../../hooks';

type Props = {
  trees: CategoryTree[];
  refreshCategoryTrees: () => void;
};

const CategoryTreesDataGrid: FC<Props> = ({trees, refreshCategoryTrees}) => {
  const translate = useTranslate();
  const router = useRouter();
  const {isGranted} = useSecurity();
  const [searchString, setSearchString] = useState('');
  const [filteredTrees, setFilteredTrees] = useState<CategoryTree[]>(trees);
  const notify = useNotify();
  const [isConfirmationModalOpen, openConfirmationModal, closeConfirmationModal] = useBooleanState();
  const [categoryTreeToDelete, setCategoryTreeToDelete] = useState<CategoryTree | null>(null);

  const followCategoryTree = useCallback((tree: CategoryTree): void => {
    const url = router.generate('pim_enrich_categorytree_tree', {id: tree.id});
    router.redirect(url);

    return;
  }, []);

  const search = useCallback(
    (searchString: string) => {
      setFilteredTrees(
        trees.filter((tree: CategoryTree) => {
          return (
            tree.code.toLocaleLowerCase().includes(searchString.toLowerCase().trim()) ||
            tree.label.toLocaleLowerCase().includes(searchString.toLowerCase().trim())
          );
        })
      );
    },
    [trees]
  );

  const deleteCategoryTree = async () => {
    if (categoryTreeToDelete) {
      const success = await deleteCategory(categoryTreeToDelete.id);
      success && refreshCategoryTrees();
      const message = success
        ? 'pim_enrich.entity.category.category_tree_deleted'
        : 'pim_enrich.entity.category.category_tree_deletion_error';
      notify(
        success ? NotificationLevel.SUCCESS : NotificationLevel.ERROR,
        translate(message, {tree: categoryTreeToDelete.label})
      );
      setCategoryTreeToDelete(null);
    }
    closeConfirmationModal();
  };

  const onDeleteCategoryTree = (categoryTree: CategoryTree) => {
    if (categoryTree.productsNumber && categoryTree.productsNumber > 100) {
      notify(NotificationLevel.INFO, translate('pim_enrich.entity.category.products_limit_exceeded', {limit: 100}));

      return;
    }

    setCategoryTreeToDelete(categoryTree);
    openConfirmationModal();
  };

  useEffect(() => {
    setFilteredTrees(trees);
    setSearchString('');
  }, [trees]);

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  const countTreesChildren = useCountCategoryTreesChildren();

  return (
    <>
      <StyledSearch searchValue={searchString} onSearchChange={onSearch} placeholder={translate('pim_common.search')}>
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredTrees.length.toString()}, filteredTrees.length)}
        </Search.ResultCount>
      </StyledSearch>
      {filteredTrees.length === 0 && searchString !== '' && (
        <NoResults
          title={translate('pim_datagrid.no_results', {
            entityHint: translate('pim_enrich.entity.category.label'),
          })}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      )}
      {filteredTrees.length > 0 && (
        <>
          <Table>
            <Table.Header>
              <Table.HeaderCell>
                {translate('pim_enrich.entity.category.content.tree_list.columns.label')}
              </Table.HeaderCell>
              <Table.HeaderCell>
                {translate('pim_enrich.entity.category.content.tree_list.columns.number_of_categories')}
              </Table.HeaderCell>
              <Table.HeaderCell />
            </Table.Header>
            <Table.Body>
              {filteredTrees.map(tree => (
                <Table.Row
                  key={tree.code}
                  onClick={isGranted('pim_enrich_product_category_list') ? () => followCategoryTree(tree) : undefined}
                >
                  <Table.Cell rowTitle>{tree.label}</Table.Cell>
                  <Table.Cell>
                    {countTreesChildren !== null &&
                    translate(
                      'pim_enrich.entity.category.content.tree_list.columns.count_categories',
                      {count: countTreesChildren.hasOwnProperty(tree.code) ? countTreesChildren[tree.code] : 0},
                      countTreesChildren.hasOwnProperty(tree.code) ? countTreesChildren[tree.code] : 0
                    )}
                  </Table.Cell>
                  <TableActionCell>
                    <Button
                      ghost
                      level="danger"
                      size={'small'}
                      onClick={() => onDeleteCategoryTree(tree)}
                      disabled={!tree.hasOwnProperty('productsNumber')}
                    >
                      {translate('pim_common.delete')}
                    </Button>
                  </TableActionCell>
                </Table.Row>
              ))}
            </Table.Body>
          </Table>
          {isConfirmationModalOpen && categoryTreeToDelete && (
            <DeleteCategoryModal
              categoryLabel={categoryTreeToDelete.label}
              closeModal={closeConfirmationModal}
              deleteCategory={deleteCategoryTree}
              message={'pim_enrich.entity.category.delete_category_tree_confirmation'}
            />
          )}
        </>
      )}
    </>
  );
};

const StyledSearch = styled(Search)`
  margin-bottom: 20px;
`;

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export {CategoryTreesDataGrid};
