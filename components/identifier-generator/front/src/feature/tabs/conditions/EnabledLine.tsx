import React from 'react';
import {Condition, EnabledCondition} from '../../models';
import {Table, TextInput, SelectInput, Button} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {ConditionIdentifier} from '../SelectionTab';

type EnabledLineProps = {
  condition: EnabledCondition & {id: ConditionIdentifier};
  onChange: (condition: Condition & {id: ConditionIdentifier}) => void;
  onDelete: () => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();

  const handleChange = (value: string) => {
    onChange({...condition, value: value === 'true'});
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
        <Button onClick={onDelete} ghost level="danger">
          {translate('pim_common.delete')}
        </Button>
      </Table.ActionCell>
    </Table.Row>
  );
};

export {EnabledLine};
