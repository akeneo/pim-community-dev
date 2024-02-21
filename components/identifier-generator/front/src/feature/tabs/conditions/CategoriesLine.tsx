import React from 'react';
import {Operator} from '../../models';
import {Button, Table} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {CategoryCode, useTranslate} from '@akeneo-pim-community/shared';
import {CategoriesSelector, OperatorSelector} from '../../components';
import {useIdentifierGeneratorAclContext} from '../../context';
import {CategoriesCondition, CategoriesOperators} from '../../models/conditions/categoriesCondition';

type CategoriesLineProps = {
  condition: CategoriesCondition;
  onChange: (condition: CategoriesCondition) => void;
  onDelete: () => void;
};

const CategoriesLine: React.FC<CategoriesLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const handleOperatorChange = (operator: Operator) => {
    const {value, ...conditionWithoutValue} = condition;
    switch (operator) {
      case Operator.IN:
      case Operator.NOT_IN:
      case Operator.IN_CHILDREN_LIST:
      case Operator.NOT_IN_CHILDREN_LIST:
        onChange({...conditionWithoutValue, operator, value: value ?? []});
        break;
      case Operator.CLASSIFIED:
      case Operator.UNCLASSIFIED:
        onChange({...conditionWithoutValue, operator});
    }
  };

  const handleCategoryCodesChange = (categoryCodes: CategoryCode[]) => {
    onChange({...condition, value: categoryCodes});
  };

  return (
    <>
      <Styled.TitleCell>{translate('pim_common.categories')}</Styled.TitleCell>
      <Styled.SelectionInputsContainer>
        <OperatorSelector
          operator={condition.operator}
          onChange={handleOperatorChange}
          operators={CategoriesOperators}
          isInSelection={true}
        />
        {(condition.operator === Operator.IN ||
          condition.operator === Operator.NOT_IN ||
          condition.operator === Operator.IN_CHILDREN_LIST ||
          condition.operator === Operator.NOT_IN_CHILDREN_LIST) && (
          <CategoriesSelector categoryCodes={condition.value} onChange={handleCategoryCodesChange} />
        )}
      </Styled.SelectionInputsContainer>
      <Table.ActionCell>
        {identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted && (
          <Button onClick={onDelete} ghost level="danger">
            {translate('pim_common.delete')}
          </Button>
        )}
      </Table.ActionCell>
    </>
  );
};

export {CategoriesLine};
