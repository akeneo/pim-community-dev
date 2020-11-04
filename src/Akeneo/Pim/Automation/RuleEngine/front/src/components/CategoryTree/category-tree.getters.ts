import {Router} from '../../dependenciesTools';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from './category-tree.types';
import {Category} from '../../models';
import {NetworkLifeCycle} from './hooks/NetworkLifeCycle.types';
import {
  fetchCategoryTree,
  fetchRootCategoryTrees,
} from '../../fetch/categoryTree.fetcher';

const getInitCategoryTreeOpenedNode = async (
  router: Router,
  categoryTree: CategoryTreeModel,
  selectedCategories: Category[],
  fnSetter: React.Dispatch<
    React.SetStateAction<NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]>>
  >
) => {
  const selectedCategoriesIds = selectedCategories.map(category => category.id);
  fnSetter(prev => ({...prev, status: 'PENDING'}));
  let response = null;
  try {
    response = await fetchCategoryTree(
      router,
      selectedCategoriesIds,
      categoryTree.id
    );
    if (response.ok) {
      const data = await response.json();
      fnSetter(prev => ({...prev, status: 'COMPLETE', data}));
      return response;
    }
  } catch (e) {
    fnSetter(prev => ({
      ...prev,
      status: 'ERROR',

      data: null,
      error: e,
    }));
    return response;
  }
  fnSetter(prev => ({...prev, status: 'COMPLETE', data: null}));
  return response;
};

const getCategoriesTrees = async (
  fnSetter: React.Dispatch<
    React.SetStateAction<NetworkLifeCycle<CategoryTreeModel[]>>
  >
) => {
  fnSetter(prev => ({...prev, status: 'PENDING'}));
  const data: CategoryTreeModel[] = await fetchRootCategoryTrees();
  fnSetter(prev => ({...prev, status: 'COMPLETE', data}));
  return data;
};

export {getInitCategoryTreeOpenedNode, getCategoriesTrees};
