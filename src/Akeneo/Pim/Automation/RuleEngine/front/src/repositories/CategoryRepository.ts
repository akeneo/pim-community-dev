import { Router } from '../dependenciesTools';
import { Category } from '../models/Category';
import { fetchCategoriesByIdentifiers } from '../fetch/CategoryFetcher';

const cacheCategories: { [identifier: string]: Category | null } = {};

export const getCategoriesByIdentifiers = async (
  categoryIdentifiers: string[],
  router: Router
): Promise<{ [identifier: string]: Category | null }> => {
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
    const result: { [identifier: string]: Category | null } = {
      ...previousValue,
    };
    result[currentValue] = cacheCategories[currentValue];
    return result;
  }, {});
};
