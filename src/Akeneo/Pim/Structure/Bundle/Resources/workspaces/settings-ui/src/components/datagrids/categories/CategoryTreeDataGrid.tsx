import React, {FC, useCallback} from 'react';
import {Table} from 'akeneo-design-system';
import {Category} from '../../../models';
import {useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  trees: Category[];
};

const CategoryTreesDataGrid: FC<Props> = ({trees}) => {
  const translate = useTranslate();
  const router = useRouter();
  const {isGranted} = useSecurity();

  const followCategoryTree = useCallback((tree: Category): void => {
    const url = router.generate('pim_enrich_categorytree_tree', {id: tree.id});
    router.redirect(url);

    return;
  }, []);

  return (
    <>
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('pim_enrich.entity.category.content.tree_list.columns.label')}</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {trees.map(tree => (
            <Table.Row key={tree.code} onClick={isGranted('pim_enrich_product_category_list') ? () => followCategoryTree(tree) : undefined}>
              <Table.Cell rowTitle>{tree.label}</Table.Cell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    </>
  );
};

export {CategoryTreesDataGrid};
