import React, { useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  CategoryCondition,
  CategoryOperators,
} from '../../../../models/conditions';
import { Operator } from '../../../../models/Operator';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { FieldColumn, OperatorColumn } from './style';
import { Category, CategoryCode } from '../../../../models';
import { getCategoriesByIdentifiers } from '../../../../repositories/CategoryRepository';
import { CategoriesSelector } from '../../../../components/Selectors/CategoriesSelector';
import {
  getInitCategoryTreeOpenedNode,
  getCategoriesTrees,
} from '../../../../components/CategoryTree/category-tree.getters';
import {
  CategoryTreeModelWithOpenBranch,
  CategoryTreeModel,
} from '../../../../components/CategoryTree/category-tree.types';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { useRegisterConst } from '../../hooks/useRegisterConst';
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';

type CategoryConditionLineProps = ConditionLineProps & {
  condition: CategoryCondition;
};

const CategoryConditionLine: React.FC<CategoryConditionLineProps> = ({
  condition,
  currentCatalogLocale,
  lineNumber,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();
  const { watch, setValue } = useFormContext();
  const [initCategoryTreeOpenBranch, setInitCategoryTreeOpenBranch] = useState<
    NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]>
  >({
    status: 'PENDING',
    data: [],
  });
  const [categoryTreeSelected, setCategoryTreeSelected] = useState<
    CategoryTreeModel
  >();
  const [categoryTrees, setCategoriesTrees] = useState<
    NetworkLifeCycle<CategoryTreeModel[]>
  >({
    status: 'PENDING',
    data: [],
  });

  const [categories, setCategories] = React.useState<Category[]>([]);

  useRegisterConst(`content.conditions[${lineNumber}].field`, condition.field);
  // TODO Fix this: this field get back to condition.value if you delete another condition
  useRegisterConst(`content.conditions[${lineNumber}].value`, condition.value);

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);
  const getValueFormValue: () => CategoryCode[] = () =>
    watch(`content.conditions[${lineNumber}].value`);
  const setValueFormValue = (value: CategoryCode[] | null) => {
    condition.value = value ?? undefined;
    setValue(`content.conditions[${lineNumber}].value`, value);
  };

  useEffect(() => {
    getCategoriesByIdentifiers(getValueFormValue() || [], router).then(
      results => {
        const categories = Object.values(results).filter(category => {
          return category !== null;
        }) as Category[];
        setCategories(categories);
      }
    );
  }, [JSON.stringify(getValueFormValue())]);

  useEffect(() => {
    const updateTree = async () => {
      const results = await getCategoriesByIdentifiers(
        getValueFormValue() || [],
        router
      );
      const categories = Object.values(results).filter(category => {
        return category !== null;
      }) as Category[];
      setCategories(categories);
      const data = await getCategoriesTrees(setCategoriesTrees);
      if (!categoryTreeSelected) {
        setCategoryTreeSelected(data[0]);
      }
      await getInitCategoryTreeOpenedNode(
        router,
        categoryTreeSelected || data[0],
        categories,
        setInitCategoryTreeOpenBranch
      );
    };
    updateTree();
  }, [categoryTreeSelected]);

  const shouldDisplayValue: () => boolean = () =>
    Operator.UNCLASSIFIED !== getOperatorFormValue();

  const handleCategoryDelete = (categoryCodeToDelete: CategoryCode) => {
    setValueFormValue(
      (getValueFormValue() || []).filter(
        categoryCode => categoryCode !== categoryCodeToDelete
      )
    );
  };

  const handlerCategorySelect = (categoryCode: string) => {
    if ((getValueFormValue() || []).includes(categoryCode)) {
      setValueFormValue(
        (getValueFormValue() || []).filter(code => !(code === categoryCode))
      );
    } else {
      setValueFormValue([...(getValueFormValue() || []), categoryCode]);
    }
  };

  return (
    <div className='AknGrid-bodyCell'>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {translate('pimee_catalog_rule.form.edit.fields.category')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          data-testid={`edit-rules-input-${lineNumber}-operator`}
          hiddenLabel={true}
          availableOperators={CategoryOperators}
          value={condition.operator}
          name={`content.conditions[${lineNumber}].operator`}
        />
      </OperatorColumn>
      {shouldDisplayValue() && (
        <CategoriesSelector
          categoryTrees={categoryTrees}
          categoryTreeSelected={categoryTreeSelected}
          setCategoryTreeSelected={setCategoryTreeSelected}
          initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
          locale={currentCatalogLocale}
          onDelete={handleCategoryDelete}
          onSelectCategory={handlerCategorySelect}
          selectedCategories={categories}
        />
      )}
    </div>
  );
};

export { CategoryConditionLine, CategoryConditionLineProps };
