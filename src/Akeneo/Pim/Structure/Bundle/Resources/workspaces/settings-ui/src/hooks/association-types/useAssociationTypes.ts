import {useState} from 'react';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {AssociationType} from '../../models';

const UserContext = require('pim/user-context');

export type AssociationTypes = {
  total: number;
  currentPage: number;
  list: AssociationType[];
};

const useAssociationTypes = () => {
  const [associationTypes, setAssociationTypes] = useState<AssociationTypes | null>(null);
  const localeCode = UserContext.get('catalogLocale');
  const router = useRouter();

  const search = async (searchString: string, sortDirection: string, page: number) => {
    const url = router.generate('pim_datagrid_load', {
      alias: 'association-type-grid',
      'association-type-grid': {
        localeCode: localeCode,
        _sort_by: {
          label: sortDirection,
        },
        _filter: {
          label: {
            value: searchString,
          },
        },
        _pager: {
          _page: page > 0 ? page : 1,
        },
      },
    });

    const result = await fetch(url, {
      method: 'GET',
      headers: [
        ['Content-type', 'application/json'],
        ['X-Requested-With', 'XMLHttpRequest'],
      ],
    });
    const response = await result.json();
    const dataGridInfo = JSON.parse(response.data);

    // Reload from page 1 if the requested page is inconsistent (i.e. no results above the first page).
    if (dataGridInfo.data.length === 0 && page > 1) {
      search(searchString, sortDirection, 1);
      return;
    }

    setAssociationTypes({
      total: dataGridInfo.options.totalRecords,
      currentPage: parseInt(response.metadata.state.currentPage),
      list: dataGridInfo.data.map((associationTypeData: any) => {
        return {
          id: associationTypeData['id'],
          label: associationTypeData['label'],
          isQuantified: associationTypeData['isQuantified'],
          isTwoWay: associationTypeData['isTwoWay'],
          editLink: associationTypeData['edit_link'],
          deleteLink: associationTypeData['delete_link'],
        };
      }),
    });
  };

  return {associationTypes, search};
};

export {useAssociationTypes};
