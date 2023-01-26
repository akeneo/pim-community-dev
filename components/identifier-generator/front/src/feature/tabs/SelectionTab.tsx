import React, {useCallback, useState} from 'react';
import {Condition, CONDITION_NAMES, Conditions, IdentifierGenerator, PROPERTY_NAMES, Target} from '../models';
import {Helper, NoResultsIllustration, Placeholder, SectionTitle, Table, TextInput, uuid} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierAttributes} from '../hooks';
import {Styled} from '../components/Styled';
import {ListSkeleton, TabValidationErrors} from '../components';
import {AddConditionButton, EnabledLine, FamilyLine, UserAddedConditionsList} from './conditions';
import {SimpleDeleteModal} from '../pages';
import {Violation} from '../validators';
import {SimpleSelectLine} from './conditions/SimpleSelectLine';
import styled from 'styled-components';

type SelectionTabProps = {
  generator: IdentifierGenerator;
  onChange: (conditions: Conditions) => void;
  validationErrors: Violation[];
};

type ConditionIdentifier = string;
type ConditionWithIdentifier = Condition & {id: ConditionIdentifier};
type ConditionsWithIdentifier = ConditionWithIdentifier[];

type ConditionLineProps = {
  condition: Condition;
  onChange: (condition: Condition) => void;
  onDelete: () => void;
};

const IsEmpty = styled.div`
  max-width: 160px;
`;

const ConditionLine: React.FC<ConditionLineProps> = ({condition, onChange, onDelete}) => {
  switch (condition.type) {
    case CONDITION_NAMES.ENABLED:
      return <EnabledLine condition={condition} onChange={onChange} onDelete={onDelete} />;
    case CONDITION_NAMES.FAMILY:
      return <FamilyLine condition={condition} onChange={onChange} onDelete={onDelete} />;
    case CONDITION_NAMES.SIMPLE_SELECT:
      return <SimpleSelectLine condition={condition} onChange={onChange} onDelete={onDelete} />;
  }
};

const SelectionTab: React.FC<SelectionTabProps> = ({generator, onChange, validationErrors}) => {
  const {conditions} = generator;
  // TODO: the skeleton does not seem to be linked to the right isLoading
  const {isLoading} = useIdentifierAttributes();
  const translate = useTranslate();
  const [conditionIdToDelete, setConditionIdToDelete] = useState<ConditionIdentifier | undefined>();
  const [conditionsWithId, setConditionsWithId] = useState<ConditionsWithIdentifier>(
    conditions.map(condition => ({
      id: uuid(),
      ...condition,
    }))
  );

  const removeIdentifier: (conditionWithId: ConditionWithIdentifier) => Condition = conditionWithId => {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const {id, ...condition} = conditionWithId;

    return condition;
  };

  const removeIdentifiers = useCallback(
    (conditionsWithId: ConditionWithIdentifier[]) => conditionsWithId.map(removeIdentifier),
    []
  );

  const handleChange = (conditionWithId: Condition & {id: string}) => {
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
  }, [closeModal, conditionIdToDelete, conditionsWithId, onChange, removeIdentifiers]);

  const onDelete = useCallback(
    (conditionId: ConditionIdentifier) => () => {
      setConditionIdToDelete(conditionId);
    },
    []
  );

  return (
    <>
      <TabValidationErrors errors={validationErrors} />
      {conditionsWithId.length > 0 && (
        <Helper level="info">
          {translate('pim_identifier_generator.selection.helper.title')}
          <Styled.ListItems>
            <li>{translate('pim_identifier_generator.selection.helper.automate')}</li>
            <li>{translate('pim_identifier_generator.selection.helper.multiple')}</li>
          </Styled.ListItems>
        </Helper>
      )}
      <SectionTitle>
        <SectionTitle.Title>{translate('pim_identifier_generator.tabs.product_selection')}</SectionTitle.Title>
        <SectionTitle.Spacer />
        <AddConditionButton conditions={conditionsWithId} onAddCondition={onAddCondition} />
      </SectionTitle>
      <Table>
        <Table.Body>
          {isLoading && <ListSkeleton />}
          {!isLoading && (
            <>
              <UserAddedConditionsList generator={generator} />
              {conditionsWithId.map(({id, ...condition}) => (
                <Table.Row key={id} aria-colspan={3}>
                  <ConditionLine
                    condition={condition}
                    onChange={condition => handleChange({...condition, id})}
                    onDelete={onDelete(id)}
                  />
                </Table.Row>
              ))}
              <Table.Row aria-colspan={3}>
                <Styled.TitleCell>
                  {identifiers && identifiers.length > 0 ? identifiers[0].label : `[${target}]`}
                </Styled.TitleCell>
                <Styled.SelectionInputsContainer>
                  <IsEmpty>
                    <TextInput value={translate('pim_common.operators.EMPTY')} readOnly={true} />
                  </IsEmpty>
                </Styled.SelectionInputsContainer>
                <Table.Cell />
              </Table.Row>
              {conditionsWithId.length === 0 && (
                <tr aria-colspan={3}>
                  <td colSpan={3}>
                    <Placeholder
                      illustration={<NoResultsIllustration />}
                      size="large"
                      title={translate('pim_identifier_generator.selection.empty.title')}
                    >
                      <Styled.BoldContainer>
                        {translate('pim_identifier_generator.selection.empty.text')}
                      </Styled.BoldContainer>
                      {translate('pim_identifier_generator.selection.empty.info')}
                    </Placeholder>
                  </td>
                </tr>
              )}
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
