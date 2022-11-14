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
import {CategoryTreeModel, Template} from '../../models';
import styled from 'styled-components';
import {NoResults} from './NoResults';
import {DeleteCategoryModal} from './DeleteCategoryModal';
import {deleteCategory} from '../../infrastructure';
import {createTemplate} from '../templates/createTemplate';
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
  const [searchString, setSearchString] = useState('');
  const [filteredTrees, setFilteredTrees] = useState<CategoryTreeModel[]>(trees);
  const notify = useNotify();
  const [isConfirmationModalOpen, openConfirmationModal, closeConfirmationModal] = useBooleanState();
  const [categoryTreeToDelete, setCategoryTreeToDelete] = useState<CategoryTreeModel | null>(null);
  const [displayCategoryTemplatesColumn, setDisplayCategoryTemplatesColumn] = useState<boolean>(false);
  const userContext = useUserContext();
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

  const onCreateTemplate = (categoryTree: CategoryTreeModel) => {
    createTemplate(categoryTree, catalogLocale, router)
      .then(response => {
        response.json().then((template: Template) => {
          if (template) {
            notify(NotificationLevel.SUCCESS, translate('akeneo.category.template.notification_success'));
            redirectToTemplate(categoryTree.id, template.uuid);
          }
        });
      })
      .catch(() => {
        notify(NotificationLevel.ERROR, translate('akeneo.category.template.notification_error'));
      });
  };

  const redirectToTemplate = (treeId: number, templateUuid: string) => {
    router.redirect(
      router.generate('pim_category_template_edit', {
        treeId: treeId,
        templateUuid: templateUuid,
      })
    );
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
    const hasRights =
      isGranted('pim_enrich_product_category_template') || isGranted('pim_enrich_product_category_edit_attributes');
    let hasTemplates = false;

    filteredTrees.map(function (tree) {
      if (tree.templateLabel) {
        hasTemplates = true;
      }

      return hasTemplates;
    });

    setDisplayCategoryTemplatesColumn(featureFlags.isEnabled('enriched_category') && hasRights && hasTemplates);
  }, [featureFlags, filteredTrees, isGranted]);

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
              {displayCategoryTemplatesColumn && (
                <Table.HeaderCell>{translate('akeneo.category.tree_list.column.category_templates')}</Table.HeaderCell>
              )}
              <StyleActionHeader isEnrichedCategoryEnabled={featureFlags.isEnabled('enriched_category')}>
                {translate('pim_enrich.entity.category.content.tree_list.columns.actions')}
              </StyleActionHeader>
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
                  {displayCategoryTemplatesColumn && <Table.Cell>{tree.templateLabel}</Table.Cell>}
                  <Table.ActionCell>
                    {featureFlags.isEnabled('enriched_category') && isGranted('pim_enrich_product_category_template') && (
                      <Button
                        ghost
                        level="tertiary"
                        size={'small'}
                        onClick={() => {
                          tree.templateUuid ? redirectToTemplate(tree.id, tree.templateUuid) : onCreateTemplate(tree);
                        }}
                        disabled={!tree.hasOwnProperty('productsNumber')}
                      >
                        {translate(
                          tree.templateUuid ? 'akeneo.category.template.edit' : 'akeneo.category.template.create'
                        )}
                      </Button>
                    )}
                    {isGranted('pim_enrich_product_category_remove') && (
                      <Button
                        ghost
                        level="danger"
                        size={'small'}
                        onClick={() => onDeleteCategoryTree(tree)}
                        disabled={!tree.hasOwnProperty('productsNumber')}
                      >
                        {translate('akeneo.category.tree.delete')}
                      </Button>
                    )}
                  </Table.ActionCell>
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

const StyleActionHeader = styled(Table.HeaderCell)<{isEnrichedCategoryEnabled: boolean}>`
  width: ${({isEnrichedCategoryEnabled}) => (isEnrichedCategoryEnabled ? '400px' : '50px')};
`;

export {CategoryTreesDataGrid};
