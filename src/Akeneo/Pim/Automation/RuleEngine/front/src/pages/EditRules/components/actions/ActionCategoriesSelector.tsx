import React, { useState } from 'react';
import { LineErrors } from '../LineErrors';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { Select2Wrapper } from '../../../../components/Select2Wrapper';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { CategorySelector } from '../../../../components/Selectors/CategorySelector';
import {
  Category,
  CategoryCode,
  CategoryId,
  LocaleCode,
} from '../../../../models';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import {
  getCategoriesByIdentifiers,
  getCategoryByIdentifier,
} from '../../../../repositories/CategoryRepository';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { getCategoriesTrees } from '../../../../components/CategoryTree/category-tree.getters';
import { Controller } from 'react-hook-form';
import { SmallHelper } from '../../../../components/HelpersInfos/SmallHelper';
import InputBoolean from '../../../../components/Inputs/InputBoolean';
import styled from 'styled-components';

const SelectorBlock = styled.div`
  margin-bottom: 15px;
`;

type Props = {
  lineNumber: number;
  currentCatalogLocale: LocaleCode;
  values: CategoryCode[];
  setValue: (value: any) => void;
  valueFormName: string;
  withIncludeChildren?: boolean;
  includeChildrenFormName?: string;
  includeChildrenValue?: boolean;
  setIncludeChildrenValue?: (value: boolean) => void;
};

const ActionCategoriesSelector: React.FC<Props> = ({
  lineNumber,
  currentCatalogLocale,
  values,
  setValue,
  valueFormName,
  withIncludeChildren = false,
  includeChildrenFormName = '',
  includeChildrenValue = false,
  setIncludeChildrenValue,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [categories, setCategories] = React.useState<
    NetworkLifeCycle<Category[]>
  >({ status: 'PENDING' });
  const [categoryTrees, setCategoriesTrees] = useState<
    NetworkLifeCycle<CategoryTreeModel[]>
  >({ status: 'PENDING' });
  const [currentCategoryTree, setCurrentCategoryTree] = useState<
    CategoryTreeModel
  >();
  const [closeTick, setCloseTick] = React.useState<boolean>(false);
  const [
    categoryTreesWithSelectedCategoriesMap,
    setCategoryTreesWithSelectedCategoriesMap,
  ] = React.useState<Map<CategoryTreeModel, Category[]>>();
  const [unexistingCategoryCodes, setUnexistingCategoryCodes] = React.useState<
    CategoryCode[]
  >([]);

  /**
   * Initialize the main object for this component. This object is a Map, having
   * - a CategoryTreeModel as key
   * - the selected categories as value
   */
  const initializeCategoryTreesWithSelectedCategories = () => {
    const categoryTreesWithSelectedCategories = (
      categoryTrees.data || []
    ).reduce<[CategoryTreeModel, Category[]][]>(
      (previousValue, categoryTree) => {
        const categoriesData: Category[] = categories.data || [];
        const matchingCategories = categoriesData.filter(
          category => category.root === categoryTree.id
        );
        if (matchingCategories.length) {
          previousValue.push([categoryTree, matchingCategories]);
        }
        return previousValue;
      },
      []
    );

    return new Map<CategoryTreeModel, Category[]>(
      categoryTreesWithSelectedCategories
    );
  };

  React.useEffect(() => {
    getCategoriesByIdentifiers(values, router).then(indexedCategories => {
      const existingCategories: Category[] = [];
      const unexistingCategoryCodes: CategoryCode[] = [];
      values.forEach(categoryCode => {
        if (indexedCategories[categoryCode]) {
          existingCategories.push(indexedCategories[categoryCode] as Category);
        } else {
          unexistingCategoryCodes.push(categoryCode);
        }
      });
      setCategories({ status: 'COMPLETE', data: existingCategories });
      setUnexistingCategoryCodes(unexistingCategoryCodes);
    });
    getCategoriesTrees(setCategoriesTrees);
  }, []);

  React.useEffect(() => {
    if (
      categoryTreesWithSelectedCategoriesMap === undefined &&
      categories.data &&
      categories.status === 'COMPLETE' &&
      categoryTrees.data &&
      categoryTrees.status === 'COMPLETE'
    ) {
      setCategoryTreesWithSelectedCategoriesMap(
        initializeCategoryTreesWithSelectedCategories()
      );
    }
  }, [categories, categoryTrees]);

  if (
    !categories.data ||
    categories.status !== 'COMPLETE' ||
    !categoryTrees.data ||
    categoryTrees.status !== 'COMPLETE' ||
    !categoryTreesWithSelectedCategoriesMap
  ) {
    return (
      <img
        src='/bundles/pimui/images//loader-V2.svg'
        alt={translate('pim_common.loading')}
      />
    );
  }

  const getSelectedCategories: () => CategoryCode[] = () => {
    if (!categoryTreesWithSelectedCategoriesMap) {
      return unexistingCategoryCodes;
    }
    return Array.from(categoryTreesWithSelectedCategoriesMap.entries())
      .reduce<CategoryCode[]>((previousValue, [_tree, categories]) => {
        return [
          ...previousValue,
          ...categories.map(category => category.code).sort(),
        ];
      }, [])
      .concat(unexistingCategoryCodes);
  };

  const handleDeleteUnexistingAttributeCode = (
    categoryCodeToDelete: CategoryCode
  ) => {
    setUnexistingCategoryCodes(
      unexistingCategoryCodes.filter(
        categoryCode => categoryCode !== categoryCodeToDelete
      )
    );
  };

  const getCurrentCategoryTreeOrDefault = () => {
    if (
      currentCategoryTree &&
      categoryTreesWithSelectedCategoriesMap.get(currentCategoryTree)
    ) {
      return currentCategoryTree;
    }

    if (categoryTreesWithSelectedCategoriesMap.size > 0) {
      return Array.from(categoryTreesWithSelectedCategoriesMap.entries())[0][0];
    }

    return null;
  };

  const handleAddCategoryTree = (categoryTreeId: CategoryId) => {
    if (!categoryTreesWithSelectedCategoriesMap) {
      return;
    }
    const categoryTree = (categoryTrees.data || []).find(
      categoryTree => categoryTree.id === categoryTreeId
    ) as CategoryTreeModel;
    categoryTreesWithSelectedCategoriesMap.set(categoryTree, []);
    setCategoryTreesWithSelectedCategoriesMap(
      new Map(categoryTreesWithSelectedCategoriesMap)
    );
    setCurrentCategoryTree(categoryTree);
  };

  const getNonSelectedCategoryTrees = () => {
    const nonSelectedCategoryTrees = (categoryTrees.data || []).filter(
      categoryTree => {
        return !Array.from(
          categoryTreesWithSelectedCategoriesMap.entries()
        ).some(([categoryTreeLeft, _categories]) => {
          return categoryTreeLeft.id === categoryTree.id;
        });
      }
    );
    return nonSelectedCategoryTrees.map(categoryTree => {
      return {
        id: categoryTree.id,
        text:
          categoryTree.labels[currentCatalogLocale] || `[${categoryTree.code}]`,
      };
    });
  };

  const handleCategorySelect = (
    categoryCode: CategoryCode,
    categoryTree: CategoryTreeModel,
    index?: number
  ) => {
    getCategoryByIdentifier(categoryCode, router).then(category => {
      if (category === null) {
        throw new Error('Category not found');
      }
      const previousCategories =
        categoryTreesWithSelectedCategoriesMap.get(categoryTree) || [];
      if (previousCategories.some(category => category.code === categoryCode)) {
        return;
      }
      if (typeof index !== 'undefined') {
        previousCategories[index] = category;
      } else {
        previousCategories.push(category);
      }
      categoryTreesWithSelectedCategoriesMap.set(
        categoryTree,
        previousCategories
      );
      setCategoryTreesWithSelectedCategoriesMap(
        new Map(categoryTreesWithSelectedCategoriesMap)
      );
      setValue(getSelectedCategories());
    });
  };

  const handleCategoryDelete = (
    categoryTree: CategoryTreeModel,
    index: number
  ) => {
    const previousCategories =
      categoryTreesWithSelectedCategoriesMap.get(categoryTree) || [];
    previousCategories.splice(index, 1);
    setCategoryTreesWithSelectedCategoriesMap(
      new Map(categoryTreesWithSelectedCategoriesMap)
    );
    setValue(getSelectedCategories());
  };

  const handleCategoryTreeDelete = (categoryTree: CategoryTreeModel) => {
    categoryTreesWithSelectedCategoriesMap.delete(categoryTree);
    setCategoryTreesWithSelectedCategoriesMap(
      new Map(categoryTreesWithSelectedCategoriesMap)
    );
    setValue(getSelectedCategories());
  };

  const getCategoryCount: (
    categoryTree: CategoryTreeModel
  ) => number = categoryTree => {
    return (categoryTreesWithSelectedCategoriesMap?.get(categoryTree) || [])
      .length;
  };

  return (
    <>
      <LineErrors lineNumber={lineNumber} type='actions' />
      {unexistingCategoryCodes.map(unexistingCategoryCode => {
        console.log({ unexistingCategoryCode });
        return (
          <SmallHelper level='error' key={unexistingCategoryCode}>
            {translate(
              'pimee_catalog_rule.exceptions.unknown_categories',
              { categoryCodes: unexistingCategoryCode },
              1
            )}
            &nbsp;
            <a
              onClick={e => {
                e.preventDefault();
                handleDeleteUnexistingAttributeCode(unexistingCategoryCode);
              }}>
              {translate(
                'pimee_catalog_rule.form.edit.actions.category.remove_unknown_category'
              )}
            </a>
          </SmallHelper>
        );
      })}
      <ActionGrid>
        <ActionLeftSide>
          <div className='AknFormContainer'>
            <Controller
              as={<input type='hidden' />}
              name={valueFormName}
              defaultValue={values}
              rules={{
                validate: () =>
                  unexistingCategoryCodes.length > 0
                    ? translate(
                        'pimee_catalog_rule.exceptions.unknown_categories',
                        { categoryCodes: unexistingCategoryCodes.join(', ') },
                        unexistingCategoryCodes.length
                      )
                    : true,
              }}
            />
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.category.select_category_trees'
              )}
            </ActionTitle>
            <label className='AknFieldContainer-label'>
              {`${translate(
                'pimee_catalog_rule.form.edit.actions.category.category_tree'
              )} ${translate('pim_common.required_label')}`}
            </label>
            <ul>
              {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map(
                ([categoryTree, _categories]) => {
                  return (
                    <li key={categoryTree.code}>
                      <button
                        data-testid={`category-tree-selector-${categoryTree.code}`}
                        className={`AknTextField AknCategoryTreeSelector${
                          getCurrentCategoryTreeOrDefault() === categoryTree
                            ? ' AknCategoryTreeSelector--selected'
                            : ''
                        }`}
                        onClick={e => {
                          e.preventDefault();
                          setCurrentCategoryTree(categoryTree);
                        }}>
                        {categoryTree.labels[currentCatalogLocale] ||
                          `[${categoryTree.code}]`}
                        <span className='AknCategoryTreeSelector-helper'>
                          {translate(
                            'pimee_catalog_rule.form.edit.actions.category.categories_selected',
                            {
                              count: getCategoryCount(categoryTree),
                            },
                            getCategoryCount(categoryTree)
                          )}
                        </span>
                        <span
                          className='AknCategoryTreeSelector-delete'
                          tabIndex={0}
                          onClick={() => handleCategoryTreeDelete(categoryTree)}
                          role='button'
                        />
                      </button>
                    </li>
                  );
                }
              )}
              {getNonSelectedCategoryTrees().length > 0 && (
                <Select2Wrapper
                  data-testid='category-tree-selector-new'
                  multiple={false}
                  label={translate(
                    'pimee_catalog_rule.form.edit.actions.category.category_tree'
                  )}
                  onSelecting={(event: any) => {
                    event.preventDefault();
                    setCloseTick(!closeTick);
                    handleAddCategoryTree(event.val);
                  }}
                  placeholder={translate(
                    'pimee_catalog_rule.form.edit.actions.category.select_category_tree'
                  )}
                  data={getNonSelectedCategoryTrees()}
                  hiddenLabel={true}
                />
              )}
            </ul>
          </div>
        </ActionLeftSide>
        <ActionRightSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.category.select_categories'
              )}
            </ActionTitle>
            <SelectorBlock>
              {getCurrentCategoryTreeOrDefault() !== null ? (
                <>
                  <label className='AknFieldContainer-label'>
                    {`${translate(
                      'pim_enrich.entity.category.plural_label'
                    )} ${translate('pim_common.required_label')}`}
                  </label>
                  <ul>
                    {(
                      categoryTreesWithSelectedCategoriesMap.get(
                        getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                      ) || []
                    ).map((category, i) => {
                      return (
                        <li key={category.code}>
                          <CategorySelector
                            data-testid={`category-selector-${category.code}`}
                            locale={currentCatalogLocale}
                            onDelete={() =>
                              handleCategoryDelete(
                                getCurrentCategoryTreeOrDefault() as CategoryTreeModel,
                                i
                              )
                            }
                            onSelectCategory={categoryCode =>
                              handleCategorySelect(
                                categoryCode,
                                getCurrentCategoryTreeOrDefault() as CategoryTreeModel,
                                i
                              )
                            }
                            selectedCategory={category}
                            categoryTreeSelected={
                              getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                            }
                          />
                        </li>
                      );
                    })}
                  </ul>
                  <CategorySelector
                    data-testid='category-selector-new'
                    locale={currentCatalogLocale}
                    onSelectCategory={categoryCode =>
                      handleCategorySelect(
                        categoryCode,
                        getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                      )
                    }
                    categoryTreeSelected={
                      getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                    }
                  />
                </>
              ) : (
                <div>
                  {translate(
                    'pimee_catalog_rule.form.edit.actions.category.no_category_tree'
                  )}
                </div>
              )}
            </SelectorBlock>
            {withIncludeChildren && (
              <SelectorBlock>
                <Controller
                  as={<input type='hidden' />}
                  name={includeChildrenFormName}
                  defaultValue={includeChildrenValue}
                />
                <InputBoolean
                  id='category-include-children'
                  label={translate('pimee_catalog_rule.rule.include_children')}
                  value={includeChildrenValue}
                  onChange={(value: boolean) => {
                    if (setIncludeChildrenValue) {
                      setIncludeChildrenValue(value);
                    }
                  }}
                />
              </SelectorBlock>
            )}
          </div>
        </ActionRightSide>
      </ActionGrid>
    </>
  );
};

export { ActionCategoriesSelector };
