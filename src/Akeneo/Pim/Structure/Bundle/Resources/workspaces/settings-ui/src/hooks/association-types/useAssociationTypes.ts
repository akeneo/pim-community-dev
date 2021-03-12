import {useState} from 'react';
import {useRouter} from '@akeneo-pim-community/legacy-bridge';
import {AssociationType} from '../../models';

const UserContext = require('pim/user-context');

const useAssociationTypes = () => {
  const [associationTypes, setAssociationTypes] = useState<AssociationType[]>([]);
  const [countAssociationTypes, setCountAssociationTypes] = useState<number>(0);

  const localeCode = UserContext.get('catalogLocale');
  const router = useRouter();

  const load = async (params: any) => {
    const url = router.generate('pim_datagrid_load', {
      ...params,
      alias: 'association-type-grid',
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

    setAssociationTypes(
      dataGridInfo.data.map((associationTypeData: any) => {
        return {
          id: associationTypeData['id'],
          label: associationTypeData['label'],
          isQuantified: associationTypeData['isQuantified'],
          isTwoWay: associationTypeData['isTwoWay'],
          editLink: associationTypeData['edit_link'],
          deleteLink: associationTypeData['delete_link'],
        };
      })
    );

    setCountAssociationTypes(dataGridInfo.options.totalRecords);
  };

  const search = (searchString: string, sortDirection: string, page: number) => {
    load({
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
          _page: page,
        },
      },
    });
  };

  return {associationTypes, countAssociationTypes, search};
};

export {useAssociationTypes};
