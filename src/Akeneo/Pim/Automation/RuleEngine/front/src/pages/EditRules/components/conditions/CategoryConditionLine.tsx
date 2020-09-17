import React, { useEffect, useState } from 'react';
import { Controller } from 'react-hook-form';
import { ConditionLineProps } from './ConditionLineProps';
import { CategoryOperators } from '../../../../models/conditions';
import { Operator } from '../../../../models/Operator';
import { OperatorSelector } from '../../../../components/Selectors/OperatorSelector';
import {
  ConditionLineErrorsContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineFormContainer,
  FieldColumn,
  OperatorColumn,
} from './style';
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
import {
  useBackboneRouter,
  useTranslate,
} from '../../../../dependenciesTools/hooks';
import { useControlledFormInputCondition } from '../../hooks';
import { LineErrors } from '../LineErrors';

const INIT_OPERATOR = Operator.IN_LIST;

const CategoryConditionLine: React.FC<ConditionLineProps> = ({
  currentCatalogLocale,
  lineNumber,
}) => {
  const translate = useTranslate();
  const router = useBackboneRouter();

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
  const {
    fieldFormName,
    operatorFormName,
    valueFormName,
    getOperatorFormValue,
    getValueFormValue,
    setValueFormValue,
    isFormFieldInError,
  } = useControlledFormInputCondition<CategoryCode[]>(lineNumber);

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
    <ConditionLineFormAndErrorsContainer className='AknGrid-bodyCell'>
      <ConditionLineFormContainer>
        <Controller
          as={<input type='hidden' value='' />}
          defaultValue='categories'
          name={fieldFormName}
        />
        <FieldColumn className={'AknGrid-bodyCell--highlight'}>
          {translate('pimee_catalog_rule.form.edit.fields.category')}
        </FieldColumn>
        <OperatorColumn>
          <Controller
            as={OperatorSelector}
            availableOperators={CategoryOperators}
            data-testid={`edit-rules-input-${lineNumber}-operator`}
            defaultValue={getOperatorFormValue() ?? INIT_OPERATOR}
            hiddenLabel={true}
            name={operatorFormName}
            value={getOperatorFormValue()}
          />
        </OperatorColumn>
        {shouldDisplayValue() && (
          <Controller
            as={CategoriesSelector}
            categoryTrees={categoryTrees}
            categoryTreeSelected={categoryTreeSelected}
            defaultValue={getValueFormValue()}
            initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
            locale={currentCatalogLocale}
            name={valueFormName}
            onDelete={handleCategoryDelete}
            onSelectCategory={handlerCategorySelect}
            selectedCategories={categories}
            setCategoryTreeSelected={setCategoryTreeSelected}
            rules={{
              required: translate('pimee_catalog_rule.exceptions.required'),
              validate: (categoryCodes: CategoryCode[]) =>
                Array.isArray(categoryCodes) && categoryCodes.length === 0
                  ? translate('pimee_catalog_rule.exceptions.required')
                  : true,
            }}
            hasError={isFormFieldInError('value')}
          />
        )}
      </ConditionLineFormContainer>
      <ConditionLineErrorsContainer>
        <LineErrors lineNumber={lineNumber} type='conditions' />
      </ConditionLineErrorsContainer>
    </ConditionLineFormAndErrorsContainer>
  );
};

export { CategoryConditionLine };
