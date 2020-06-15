import React, { useState } from 'react';
import { AddCategoriesAction } from '../../../../models/actions';
import { ActionLineProps } from './ActionLineProps';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { CategoryTreeModel } from '../../../../components/CategoryTree/category-tree.types';
import { Category, CategoryCode, CategoryId } from '../../../../models';
import {
  getCategoriesByIdentifiers,
  getCategoryByIdentifier,
} from '../../../../repositories/CategoryRepository';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { getCategoriesTrees } from '../../../../components/CategoryTree/category-tree.getters';
import { ActionTemplate } from './ActionTemplate';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import { LineErrors } from '../LineErrors';
import { Select2Wrapper } from '../../../../components/Select2Wrapper';
import { CategorySelector } from '../../../../components/Selectors/CategorySelector';
import { useFormContext } from 'react-hook-form';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import { SmallErrorHelper } from '../style';

type Props = {
  action: AddCategoriesAction;
} & ActionLineProps;

const AddCategoriesActionLine: React.FC<Props> = ({
  lineNumber,
  action,
  currentCatalogLocale,
  handleDelete,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const {
    setValue,
    register,
    formState,
    control,
    unregister,
  } = useFormContext();

  useRegisterConst(`content.actions[${lineNumber}].type`, 'add');
  useRegisterConst(`content.actions[${lineNumber}].field`, 'categories');

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

  React.useEffect(() => {
    register(
      { name: `content.actions[${lineNumber}].items` },
      {
        validate: () =>
          unexistingCategoryCodes.length > 0
            ? translate(
                'pimee_catalog_rule.exceptions.unknown_categories',
                { categoryCodes: unexistingCategoryCodes.join(', ') },
                unexistingCategoryCodes.length
              )
            : true,
      }
    );
  }, [unexistingCategoryCodes]);

  React.useEffect(() => {
    setValue(
      `content.actions[${lineNumber}].items`,
      control.defaultValuesRef?.current?.content?.actions[lineNumber]?.items
    );
    return () => {
      unregister(`content.actions[${lineNumber}].items`);
    };
  }, [formState.submitCount]);

  React.useEffect(() => {
    getCategoriesByIdentifiers(action.items || [], router).then(
      indexedCategories => {
        const existingCategories: Category[] = [];
        const unexistingCategoryCodes: CategoryCode[] = [];
        action.items.forEach(categoryCode => {
          if (indexedCategories[categoryCode]) {
            existingCategories.push(
              indexedCategories[categoryCode] as Category
            );
          } else {
            unexistingCategoryCodes.push(categoryCode);
          }
        });
        setCategories({ status: 'COMPLETE', data: existingCategories });
        setUnexistingCategoryCodes(unexistingCategoryCodes);
      }
    );
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
      setValue(`content.actions[${lineNumber}].items`, getSelectedCategories());
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
    setValue(`content.actions[${lineNumber}].items`, getSelectedCategories());
  };

  const handleCategoryTreeDelete = (categoryTree: CategoryTreeModel) => {
    categoryTreesWithSelectedCategoriesMap.delete(categoryTree);
    setCategoryTreesWithSelectedCategoriesMap(
      new Map(categoryTreesWithSelectedCategoriesMap)
    );
    setValue(`content.actions[${lineNumber}].items`, getSelectedCategories());
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

  const getCategoryCount: (
    categoryTree: CategoryTreeModel
  ) => number = categoryTree => {
    return (categoryTreesWithSelectedCategoriesMap?.get(categoryTree) || [])
      .length;
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

  return (
    <ActionTemplate
      title={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.title'
      )}
      helper={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.helper'
      )}
      legend={translate(
        'pimee_catalog_rule.form.edit.actions.add_category.helper'
      )}
      handleDelete={handleDelete}>
      <LineErrors lineNumber={lineNumber} type='actions' />
      <SmallErrorHelper>
        {unexistingCategoryCodes.map(unexistingCategoryCode => {
          return (
            <li key={unexistingCategoryCode}>
              {translate(
                'pimee_catalog_rule.exceptions.unknown_categories',
                { categoryCodes: unexistingCategoryCode },
                1
              )}
              <a
                onClick={e => {
                  e.preventDefault();
                  handleDeleteUnexistingAttributeCode(unexistingCategoryCode);
                }}>
                {translate(
                  'pimee_catalog_rule.form.edit.actions.add_category.remove_unknown_category'
                )}
              </a>
            </li>
          );
        })}
      </SmallErrorHelper>
      <ActionGrid>
        <ActionLeftSide>
          <div className='AknFormContainer'>
            <ActionTitle>
              {translate(
                'pimee_catalog_rule.form.edit.actions.add_category.select_category_trees'
              )}
            </ActionTitle>
            <label className='AknFieldContainer-label'>
              {`${translate(
                'pimee_catalog_rule.form.edit.actions.add_category.category_tree'
              )} ${translate('pim_common.required_label')}`}
            </label>
            <ul>
              {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map(
                ([categoryTree, _categories]) => {
                  return (
                    <li key={categoryTree.code}>
                      <button
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
                            'pimee_catalog_rule.form.edit.actions.add_category.categories_selected',
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
                  multiple={false}
                  label={translate(
                    'pimee_catalog_rule.form.edit.actions.add_category.category_tree'
                  )}
                  onSelecting={(event: any) => {
                    event.preventDefault();
                    setCloseTick(!closeTick);
                    handleAddCategoryTree(event.val);
                  }}
                  placeholder={translate(
                    'pimee_catalog_rule.form.edit.actions.add_category.select_category_tree'
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
                'pimee_catalog_rule.form.edit.actions.add_category.select_categories'
              )}
            </ActionTitle>
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
                  'pimee_catalog_rule.form.edit.actions.add_category.no_category_tree'
                )}
              </div>
            )}
          </div>
        </ActionRightSide>
      </ActionGrid>
    </ActionTemplate>
  );
};

export { AddCategoriesActionLine };
