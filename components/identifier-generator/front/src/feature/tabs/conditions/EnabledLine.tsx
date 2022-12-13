import React, {useState} from 'react';
import {Condition, Enabled} from '../../models';
import {Table, TextInput, SelectInput, Button} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DeletePropertyModal} from '../../pages';
import {ConditionId} from '../SelectionTab';

type EnabledLineProps = {
  condition: Enabled & {id: string};
  onChange: (condition: Condition & {id: string}) => void;
  onDelete: (conditionId: string) => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();
  const [enabledIdToDelete, setEnabledIdToDelete] = useState<ConditionId | undefined>();

  const handleChange = (value: string) => {
    onChange({...condition, value: value === 'true'});
  };

  const openModal = (enabledId: ConditionId) => () => {
    setEnabledIdToDelete(enabledId);
  };

  const closeModal = () => {
    setEnabledIdToDelete(undefined);
  };

  const handleDelete = () => {
    if (enabledIdToDelete) {
      onDelete(enabledIdToDelete);
      setEnabledIdToDelete(undefined);
    }
  };

  return (
    <Table.Row>
      <Styled.TitleCell>{translate('pim_common.status')}</Styled.TitleCell>
      <Styled.CellInputContainer>
        <Styled.InputContainer>
          <TextInput value={translate('pim_common.operators.=')} readOnly={true} />
        </Styled.InputContainer>
      </Styled.CellInputContainer>
      <Table.Cell>
        <Styled.InputContainer>
          <SelectInput
            value={typeof condition.value === 'undefined' ? null : condition.value ? 'true' : 'false'}
            emptyResultLabel={translate('pim_common.no_result')}
            openLabel={'pim_common.open'}
            placeholder={translate('pim_identifier_generator.selection.settings.enabled.placeholder')}
            onChange={handleChange}
            clearable={false}
          >
            <SelectInput.Option value="true" title={translate('pim_common.enabled')}>
              {translate('pim_common.enabled')}
            </SelectInput.Option>
            <SelectInput.Option
              value="false"
              title={translate('pim_identifier_generator.selection.settings.enabled.disabled')}
            >
              {translate('pim_identifier_generator.selection.settings.enabled.disabled')}
            </SelectInput.Option>
          </SelectInput>
        </Styled.InputContainer>
      </Table.Cell>
      <Table.ActionCell>
        <Button onClick={openModal(condition.id)} ghost level="danger">
          {translate('pim_common.delete')}
        </Button>
      </Table.ActionCell>
      {enabledIdToDelete && <DeletePropertyModal onClose={closeModal} onDelete={handleDelete} />}
    </Table.Row>
  );
};

export {EnabledLine};
