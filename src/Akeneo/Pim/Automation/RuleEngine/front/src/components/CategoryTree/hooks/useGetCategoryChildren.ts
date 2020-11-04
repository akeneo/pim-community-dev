import {useEffect} from 'react';
import {NetworkLifeCycle} from './NetworkLifeCycle.types';
import {useBackboneRouter} from '../../../dependenciesTools/hooks';
import {fetchCategoryTreeChildren} from '../../../fetch/categoryTree.fetcher';
import {CategoryTreeNodeModel} from '../category-tree.types';

const useGetCategoryChildren = (
  fnSetter: React.Dispatch<
    React.SetStateAction<NetworkLifeCycle<CategoryTreeNodeModel[]>>
  >,
  locale: string,
  categoryId: number,
  opened: boolean,
  branch: boolean
) => {
  const router = useBackboneRouter();
  useEffect(() => {
    if (!opened || !branch) {
      return;
    }
    const getCategoryChildren = async () => {
      fnSetter({status: 'PENDING', data: []});
      const response = await fetchCategoryTreeChildren(
        router,
        locale,
        categoryId
      );
      if (response.ok) {
        const data: CategoryTreeNodeModel = await response.json();
        fnSetter({
          status: 'COMPLETE',
          data: data?.children && Object.values(data.children),
        });
      }
      return response;
    };
    getCategoryChildren();
  }, [categoryId, opened, locale, router, branch, fnSetter]);
};

export {useGetCategoryChildren};
