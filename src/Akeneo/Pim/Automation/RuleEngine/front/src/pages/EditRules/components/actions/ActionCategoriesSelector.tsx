import React, {useState} from 'react';
import {
  ActionGrid,
  ActionLeftSide,
  ActionRightSide,
  ActionTitle,
} from './ActionLine';
import {Select2Wrapper} from '../../../../components/Select2Wrapper';
import {CategoryTreeModel} from '../../../../components/CategoryTree/category-tree.types';
import {CategorySelector} from '../../../../components/Selectors/CategorySelector';
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
import {NetworkLifeCycle} from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import {getCategoriesTrees} from '../../../../components/CategoryTree/category-tree.getters';
import {Controller} from 'react-hook-form';
import {SmallHelper} from '../../../../components/HelpersInfos/SmallHelper';
import InputBoolean from '../../../../components/Inputs/InputBoolean';
import styled from 'styled-components';
import {Label} from '../../../../components/Labels';
import {
  HelperContainer,
  InlineHelper,
} from '../../../../components/HelpersInfos/InlineHelper';
import {useControlledFormInputAction} from '../../hooks';
import {useFormContext} from 'react-hook-form';

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
  valueRequired?: boolean;
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
  valueRequired = false,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const [categories, setCategories] = React.useState<
    NetworkLifeCycle<Category[]>
  >({status: 'PENDING'});
  const [categoryTrees, setCategoriesTrees] = useState<
    NetworkLifeCycle<CategoryTreeModel[]>
  >({status: 'PENDING'});
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
  const {isFormFieldInError} = useControlledFormInputAction<string>(lineNumber);
  const {clearError} = useFormContext();

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
      setCategories({status: 'COMPLETE', data: existingCategories});
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
      {unexistingCategoryCodes.map(unexistingCategoryCode => {
        return (
          <SmallHelper level='error' key={unexistingCategoryCode}>
            {translate(
              'pimee_catalog_rule.exceptions.unknown_categories',
              {categoryCodes: unexistingCategoryCode},
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
          <Controller
            as={<input type='hidden' />}
            name={valueFormName}
            defaultValue={values}
            rules={{
              validate: (selectedCategories: CategoryCode[] | null) => {
                if (
                  valueRequired &&
                  (!selectedCategories || 0 === selectedCategories.length)
                ) {
                  return translate(
                    'pimee_catalog_rule.exceptions.required_categories'
                  );
                }

                return unexistingCategoryCodes.length
                  ? translate(
                      'pimee_catalog_rule.exceptions.unknown_categories',
                      {categoryCodes: unexistingCategoryCodes.join(', ')},
                      unexistingCategoryCodes.length
                    )
                  : true;
              },
            }}
          />
          <ActionTitle>
            {translate(
              'pimee_catalog_rule.form.edit.actions.category.select_category_trees'
            )}
          </ActionTitle>
          <Label
            className='AknFieldContainer-label control-label'
            label={`${translate(
              'pimee_catalog_rule.form.edit.actions.category.category_tree'
            )} ${translate('pim_common.required_label')}`}
          />
          <ul>
            {Array.from(categoryTreesWithSelectedCategoriesMap.entries()).map(
              ([categoryTree, _categories]) => {
                return (
                  <li
                    key={categoryTree.code}
                    className={'AknBadgedSelector-item'}>
                    <button
                      data-testid={`category-tree-selector-${categoryTree.code}`}
                      className={`AknTextField AknBadgedSelector${
                        getCurrentCategoryTreeOrDefault() === categoryTree
                          ? ' AknBadgedSelector--selected'
                          : ''
                      }`}
                      onClick={e => {
                        e.preventDefault();
                        setCurrentCategoryTree(categoryTree);
                      }}>
                      {categoryTree.labels[currentCatalogLocale] ||
                        `[${categoryTree.code}]`}
                      <span className='AknBadgedSelector-helper'>
                        {translate(
                          'pimee_catalog_rule.form.edit.actions.category.categories_selected',
                          {
                            count: getCategoryCount(categoryTree),
                          },
                          getCategoryCount(categoryTree)
                        )}
                      </span>
                      <span
                        className='AknBadgedSelector-delete'
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
              <div
                className={
                  categoryTreesWithSelectedCategoriesMap.size === 0
                    ? 'AknBadgedSelector-new'
                    : 'AknBadgedSelector-item'
                }>
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
              </div>
            )}
          </ul>
        </ActionLeftSide>
        <ActionRightSide>
          <ActionTitle>
            {translate(
              'pimee_catalog_rule.form.edit.actions.category.select_categories'
            )}
          </ActionTitle>
          <SelectorBlock
            className={
              isFormFieldInError(
                valueFormName.replace(`content.actions[${lineNumber}].`, '')
              )
                ? 'category-container-error'
                : ''
            }>
            {getCurrentCategoryTreeOrDefault() !== null ? (
              <>
                <Label
                  className='AknFieldContainer-label control-label'
                  label={`${translate(
                    'pim_enrich.entity.category.plural_label'
                  )} ${translate('pim_common.required_label')}`}
                />
                <ul>
                  {(
                    categoryTreesWithSelectedCategoriesMap.get(
                      getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                    ) || []
                  ).map((category, i) => {
                    return (
                      <li
                        key={category.code}
                        className={'AknBadgedSelector-item'}>
                        <CategorySelector
                          data-testid={`category-selector-${category.code}`}
                          locale={currentCatalogLocale}
                          onDelete={() => {
                            clearError(valueFormName);
                            handleCategoryDelete(
                              getCurrentCategoryTreeOrDefault() as CategoryTreeModel,
                              i
                            );
                          }}
                          onSelectCategory={categoryCode => {
                            const categoryTree = getCurrentCategoryTreeOrDefault() as CategoryTreeModel;
                            if (categoryCode === categoryTree.code) {
                              return;
                            }
                            clearError(valueFormName);
                            handleCategorySelect(categoryCode, categoryTree, i);
                          }}
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
                  onSelectCategory={categoryCode => {
                    const categoryTree = getCurrentCategoryTreeOrDefault() as CategoryTreeModel;
                    if (categoryCode === categoryTree.code) {
                      return;
                    }
                    clearError(valueFormName);
                    handleCategorySelect(categoryCode, categoryTree);
                  }}
                  categoryTreeSelected={
                    getCurrentCategoryTreeOrDefault() as CategoryTreeModel
                  }
                />
              </>
            ) : (
              <HelperContainer>
                <InlineHelper>
                  {translate(
                    'pimee_catalog_rule.form.edit.actions.category.no_category_tree'
                  )}
                </InlineHelper>
              </HelperContainer>
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
        </ActionRightSide>
      </ActionGrid>
    </>
  );
};

export {ActionCategoriesSelector};
