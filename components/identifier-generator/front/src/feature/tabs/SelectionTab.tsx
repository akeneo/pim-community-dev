import React, {useCallback, useState} from 'react';
import {Condition, CONDITION_NAMES, Conditions, Target} from '../models';
import {SectionTitle, Table, TextInput, uuid} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierAttributes} from '../hooks';
import {Styled} from '../components/Styled';
import {ListSkeleton} from '../components';
import {AddConditionButton, EnabledLine} from './conditions';
import {SimpleDeleteModal} from '../pages';

type SelectionTabProps = {
  conditions: Conditions;
  target: Target;
  onChange: (conditions: Conditions) => void;
};

type ConditionIdentifier = string;
type ConditionsWithIdentifier = (Condition & {id: ConditionIdentifier})[];

const SelectionTab: React.FC<SelectionTabProps> = ({target, conditions, onChange}) => {
  const translate = useTranslate();
  const {data: identifiers, isLoading} = useIdentifierAttributes();
  const [conditionIdToDelete, setConditionIdToDelete] = useState<ConditionIdentifier | undefined>();
  const [conditionsWithId, setConditionsWithId] = useState<ConditionsWithIdentifier>(
    conditions.map(condition => ({
      id: uuid(),
      ...condition,
    }))
  );

  const removeIdentifiers: (conditionsWithId: ConditionsWithIdentifier) => Conditions = conditionsWithId => {
    return conditionsWithId.map(conditionWithId => {
      // eslint-disable-next-line @typescript-eslint/no-unused-vars
      const {id, ...condition} = conditionWithId;

      return condition;
    });
  };

  const handleChange = (conditionWithId: Condition & {id: ConditionIdentifier}) => {
    const index = conditionsWithId.findIndex(condition => condition.id === conditionWithId.id);
    const newConditions = [...conditionsWithId];
    newConditions[index] = conditionWithId;
    setConditionsWithId(newConditions);
    onChange(removeIdentifiers(newConditions));
  };

  const onAddCondition = (condition: Condition) => {
    const newConditionId = uuid();
    const newConditions = [...conditionsWithId, {...condition, id: newConditionId}];
    setConditionsWithId(newConditions);
    onChange(removeIdentifiers(newConditions));
  };

  const closeModal = useCallback(() => {
    setConditionIdToDelete(undefined);
  }, []);

  const handleDeleteCondition = useCallback((): void => {
    if (conditionIdToDelete) {
      const newConditions = conditionsWithId.filter(condition => condition.id !== conditionIdToDelete);
      setConditionsWithId(newConditions);
      onChange(removeIdentifiers(newConditions));
      setConditionIdToDelete(undefined);
    }

    closeModal();
  }, [closeModal, conditionIdToDelete, conditionsWithId, onChange]);

  const onDelete = useCallback((conditionId: ConditionIdentifier) => () => {
    setConditionIdToDelete(conditionId);
  }, []);

  return (
    <>
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.tabs.product_selection')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddConditionButton onAddCondition={onAddCondition} />
      </SectionTitle>
      <Table>
        <Table.Body>
          {isLoading && <ListSkeleton />}
          {!isLoading && (
            <>
              <Table.Row>
                <Styled.TitleCell>
                  {identifiers && identifiers.length > 0 ? identifiers[0].label : `[${target}]`}
                </Styled.TitleCell>
                <Table.Cell colSpan={2}>
                  <Styled.InputContainer>
                    <TextInput value={translate('pim_common.operators.EMPTY')} readOnly={true} />
                  </Styled.InputContainer>
                </Table.Cell>
              </Table.Row>
              {conditionsWithId.map(conditionWithId => (
                <>
                  {conditionWithId.type === CONDITION_NAMES.ENABLED && (
                    <EnabledLine condition={conditionWithId} key={conditionWithId.id}
                                 onChange={handleChange} onDelete={onDelete(conditionWithId.id)}/>
                  )}
                </>
              ))}
            </>
          )}
        </Table.Body>
      </Table>
      {conditionIdToDelete && <SimpleDeleteModal onClose={closeModal} onDelete={handleDeleteCondition} />}
    </>
  );
};

export {SelectionTab};
export type {ConditionIdentifier};
