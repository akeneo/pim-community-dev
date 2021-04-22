import React, {FC, useCallback, useState} from 'react';
import {Search, Table} from 'akeneo-design-system';
import {useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTree} from '../../../models';
import {useDebounceCallback} from '@akeneo-pim-community/shared';
import styled from 'styled-components';
import {NoResults} from "../../shared";

type Props = {
  trees: CategoryTree[];
};

const CategoryTreesDataGrid: FC<Props> = ({trees}) => {
  const translate = useTranslate();
  const router = useRouter();
  const {isGranted} = useSecurity();
  const [searchString, setSearchString] = useState('');
  const [filteredTrees, setFilteredTrees] = useState<CategoryTree[]>(trees);

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

  const debouncedSearch = useDebounceCallback(search, 300);

  const onSearch = (searchValue: string) => {
    setSearchString(searchValue);
    debouncedSearch(searchValue);
  };

  return (
    <>
      <StyledSearch searchValue={searchString} onSearchChange={onSearch} placeholder={translate('pim_common.search')}>
        <Search.ResultCount>
          {translate('pim_common.result_count', {itemsCount: filteredTrees.length.toString()}, filteredTrees.length)}
        </Search.ResultCount>
      </StyledSearch>
      {filteredTrees.length === 0 && searchString !== '' &&
        <NoResults
          title={translate('pim_datagrid.no_results', {
            entityHint: translate('pim_enrich.entity.category.label'),
          })}
          subtitle={translate('pim_datagrid.no_results_subtitle')}
        />
      }
      {filteredTrees.length > 0 &&
        <Table>
          <Table.Header>
            <Table.HeaderCell>{translate('pim_enrich.entity.category.content.tree_list.columns.label')}</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {filteredTrees.map(tree => (
              <Table.Row
                key={tree.code}
                onClick={isGranted('pim_enrich_product_category_list') ? () => followCategoryTree(tree) : undefined}
              >
                <Table.Cell rowTitle>{tree.label}</Table.Cell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      }
    </>
  );
};

const StyledSearch = styled(Search)`
  margin-bottom: 20px;
`;

export {CategoryTreesDataGrid};
