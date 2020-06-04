import React, { useEffect, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import {
  CategoryCondition,
  CategoryOperators,
} from '../../../../models/conditions/CategoryCondition';
import { Operator } from '../../../../models/Operator';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import { FieldColumn, OperatorColumn } from './style';
import { useValueInitialization } from '../../hooks/useValueInitialization';
import { Category, CategoryCode } from '../../../../models/Category';
import { getCategoriesByIdentifiers } from '../../../../repositories/CategoryRepository';
import { CategoryTreeFilterCondition } from './CategoryTreeFilterCondition';
import {
  getInitCategoryTreeOpenedNode,
  getCategoriesTrees,
} from '../../../../components/CategoryTree/category-tree.getters';
import {
  CategoryTreeModelWithOpenBranch,
  CategoryTreeModel,
} from '../../../../components/CategoryTree/category-tree.types';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';

type CategoryConditionLineProps = ConditionLineProps & {
  condition: CategoryCondition;
};

const CategoryConditionLine: React.FC<CategoryConditionLineProps> = ({
  condition,
  currentCatalogLocale,
  lineNumber,
  router,
  translate,
}) => {
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
  const [categoriesTrees, setCategoriesTrees] = useState<
    NetworkLifeCycle<CategoryTreeModel[]>
  >({
    status: 'PENDING',
    data: [],
  });

  const [categories, setCategories] = React.useState<Category[]>([]);
  useValueInitialization(`content.conditions[${lineNumber}]`, {
    field: condition.field,
    operator: condition.operator,
    value: condition.value,
  });

  const getOperatorFormValue: () => Operator = () =>
    watch(`content.conditions[${lineNumber}].operator`);
  const getValueFormValue: () => CategoryCode[] = () =>
    watch(`content.conditions[${lineNumber}].value`);

  useEffect(() => {
    getCategoriesByIdentifiers(getValueFormValue(), router).then(results => {
      const categories = Object.values(results).filter(category => {
        return category !== null;
      }) as Category[];
      setCategories(categories);
    });
  }, [getValueFormValue()]);

  useEffect(() => {
    const updateTree = async () => {
      const results = await getCategoriesByIdentifiers(
        getValueFormValue(),
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

  const setValueFormValue = (value: CategoryCode[] | null) =>
    setValue(`content.conditions[${lineNumber}].value`, value);
  const setOperatorFormValue = (value: Operator) => {
    setValue(`content.conditions[${lineNumber}].operator`, value);
    if (!shouldDisplayValue()) {
      setValueFormValue(null);
    }
  };

  const handleCategoryDelete = (categoryCodeToDelete: CategoryCode) => {
    setValueFormValue(
      getValueFormValue().filter(
        categoryCode => categoryCode !== categoryCodeToDelete
      )
    );
  };

  const handlerCategorySelect = (categoryCode: string) => {
    if (getValueFormValue().includes(categoryCode)) {
      setValueFormValue(
        getValueFormValue().filter(code => !(code === categoryCode))
      );
    } else {
      setValueFormValue([...getValueFormValue(), categoryCode]);
    }
  };

  return (
    <div className='AknGrid-bodyCell'>
      <FieldColumn className={'AknGrid-bodyCell--highlight'}>
        {translate('pimee_catalog_rule.form.edit.fields.category')}
      </FieldColumn>
      <OperatorColumn>
        <OperatorSelector
          id={`edit-rules-input-${lineNumber}-operator`}
          label='Operator'
          hiddenLabel={true}
          availableOperators={CategoryOperators}
          translate={translate}
          value={getOperatorFormValue()}
          onChange={setOperatorFormValue}
        />
      </OperatorColumn>
      {shouldDisplayValue() && (
        <CategoryTreeFilterCondition
          categoriesTrees={categoriesTrees}
          categoryTreeSelected={categoryTreeSelected}
          setCategoryTreeSelected={setCategoryTreeSelected}
          initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
          locale={currentCatalogLocale}
          onDelete={handleCategoryDelete}
          onSelectCategory={handlerCategorySelect}
          selectedCategories={categories}
          translate={translate}
        />
      )}
    </div>
  );
};

export { CategoryConditionLine, CategoryConditionLineProps };
