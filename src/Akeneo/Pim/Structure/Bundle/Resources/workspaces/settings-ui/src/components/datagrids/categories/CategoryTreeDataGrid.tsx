import React, {FC} from 'react';
import {Table} from 'akeneo-design-system';
import {Category} from '../../../models';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

type Props = {
  trees: Category[];
};

const CategoryTreesDataGrid: FC<Props> = ({trees}) => {
  const translate = useTranslate();

  return (
    <>
      <Table>
        <Table.Header>
          <Table.HeaderCell>{translate('pim_enrich.entity.category.content.tree_list.columns.label')}</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {trees.map(tree => (
            <Table.Row key={tree.code}>
              <Table.Cell rowTitle>{tree.label}</Table.Cell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    </>
  );
};

export {CategoryTreesDataGrid};
