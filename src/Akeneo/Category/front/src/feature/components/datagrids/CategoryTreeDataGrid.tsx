import React, {FC, useCallback, useEffect, useState} from 'react';
import {Button, Search, Table, useBooleanState} from 'akeneo-design-system';
import {
  NotificationLevel,
  useDebounceCallback,
  useFeatureFlags,
  useNotify,
  useRouter,
  useSecurity,
  useTranslate,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../../models';
import styled from 'styled-components';
import {NoResults} from './NoResults';
import {DeleteCategoryModal} from './DeleteCategoryModal';
import {deleteCategory} from '../../infrastructure';
import {useCountCategoryTreesChildren} from '../../hooks';

type Props = {
  trees: CategoryTreeModel[];
  refreshCategoryTrees: () => void;
};

const CategoryTreesDataGrid: FC<Props> = ({trees, refreshCategoryTrees}) => {
  const translate = useTranslate();
  const router = useRouter();
  const featureFlags = useFeatureFlags();
  const {isGranted} = useSecurity();
  const userContext = useUserContext();
  const [searchString, setSearchString] = useState('');
  const [filteredTrees, setFilteredTrees] = useState<CategoryTreeModel[]>(trees);
  const notify = useNotify();
  const [isConfirmationModalOpen, openConfirmationModal, closeConfirmationModal] = useBooleanState();
  const [categoryTreeToDelete, setCategoryTreeToDelete] = useState<CategoryTreeModel | null>(null);
  const [hasTemplates, setHasTemplates] = useState<boolean>(false);
  const catalogLocale = userContext.get('catalogLocale');

  const followCategoryTree = useCallback(
    (tree: CategoryTreeModel): void => {
      const url = router.generate('pim_enrich_categorytree_tree', {id: tree.id});
      router.redirect(url);

      return;
    },
    [router]
  );

  const search = useCallback(
    (searchString: string) => {
      setFilteredTrees(
        trees.filter((tree: CategoryTreeModel) => {
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
      const response = await deleteCategory(router, categoryTreeToDelete.id);
      response.ok && refreshCategoryTrees();
      const message = response.ok
        ? 'pim_enrich.entity.category.category_tree_deletion.success'
        : response.errorMessage || 'pim_enrich.entity.category.category_tree_deletion.error';
      notify(
        response.ok ? NotificationLevel.SUCCESS : NotificationLevel.ERROR,
        translate(message, {tree: categoryTreeToDelete.label})
      );
      setCategoryTreeToDelete(null);
    }
    closeConfirmationModal();
  };

  const onDeleteCategoryTree = (categoryTree: CategoryTreeModel) => {
    if (categoryTree.productsNumber && categoryTree.productsNumber > 100) {
      notify(
        NotificationLevel.INFO,
        translate('pim_enrich.entity.category.category_tree_deletion.products_limit_exceeded.title'),
        translate('pim_enrich.entity.category.category_tree_deletion.products_limit_exceeded.message', {
          tree: categoryTree.label,
          limit: 100,
        })
      );

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

  useEffect(() => {
    let hasTemplates = false;

    filteredTrees.map(function (tree) {
      if (tree.templateLabel) {
        hasTemplates = true;
      }

      return hasTemplates;
    });

    setHasTemplates(hasTemplates);
  }, [filteredTrees]);

  const displayCategoryTemplatesColumn = () => {
    const hasRights =
      isGranted('pim_enrich_product_category_template') || isGranted('pim_enrich_product_category_edit_attributes');

    return featureFlags.isEnabled('enriched_category') && hasRights && hasTemplates;
  };

  return (
    <>
      <StyledSearch
        sticky={0}
        searchValue={searchString}
        onSearchChange={onSearch}
        placeholder={translate('pim_common.search')}
      >
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
              {displayCategoryTemplatesColumn() && (
                <Table.HeaderCell>{translate('akeneo.category.tree_list.column.category_templates')}</Table.HeaderCell>
              )}
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
                  {displayCategoryTemplatesColumn() && <Table.Cell>{tree.templateLabel}</Table.Cell>}
                  <TableActionCell>
                    {isGranted('pim_enrich_product_category_remove') && (
                      <Button
                        ghost
                        level="danger"
                        size={'small'}
                        onClick={() => onDeleteCategoryTree(tree)}
                        disabled={!tree.hasOwnProperty('productsNumber')}
                      >
                        {translate('pim_common.delete')}
                      </Button>
                    )}
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
              message={'pim_enrich.entity.category.category_tree_deletion.confirmation'}
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
