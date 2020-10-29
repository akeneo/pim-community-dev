import {Router} from '../dependenciesTools';
import {Category, CategoryCode} from '../models/Category';
import {fetchCategoriesByIdentifiers} from '../fetch/CategoryFetcher';

const cacheCategories: {[identifier: string]: Category | null} = {};

export const clearCategoryRepositoryCache = () => {
  for (const key in cacheCategories) {
    delete cacheCategories[key];
  }
};

export const getCategoriesByIdentifiers = async (
  categoryIdentifiers: CategoryCode[],
  router: Router
): Promise<{[identifier: string]: Category | null}> => {
  if (categoryIdentifiers === undefined) {
    throw new Error(
      'getCategoriesByIdentifiers cannot be called with undefined parameter'
    );
  }
  const categoryIdentifiersToGet = categoryIdentifiers.filter(
    categoryIdentifier => {
      return !Object.keys(cacheCategories).includes(categoryIdentifier);
    }
  );

  if (categoryIdentifiersToGet.length) {
    const categories = await fetchCategoriesByIdentifiers(
      categoryIdentifiersToGet,
      router
    );
    categoryIdentifiersToGet.forEach(categoryIdentifier => {
      const matchingCategory = categories.find((category: Category) => {
        return category.code === categoryIdentifier;
      });
      cacheCategories[categoryIdentifier] = matchingCategory || null;
    });
  }

  return categoryIdentifiers.reduce((previousValue, currentValue) => {
    const result: {[identifier: string]: Category | null} = {
      ...previousValue,
    };
    result[currentValue] = cacheCategories[currentValue];
    return result;
  }, {});
};

export const getCategoryByIdentifier = async (
  categoryIdentifier: CategoryCode,
  router: Router
): Promise<Category | null> => {
  await getCategoriesByIdentifiers([categoryIdentifier], router);

  return cacheCategories[categoryIdentifier];
};
