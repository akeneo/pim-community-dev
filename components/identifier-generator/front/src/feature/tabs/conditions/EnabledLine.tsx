import React from 'react';
import {Condition, Enabled} from '../../models';
import {Table, TextInput, SelectInput} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';

type EnabledLineProps = {
  condition: Enabled & {id: string};
  onChange: (condition: Condition & {id: string}) => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange, ...rest}) => {
  const translate = useTranslate();

  const handleChange = (value: string) => {
    onChange({...condition, value: value === 'true'});
  };

  return (
    <Table.Row {...rest}>
      <Styled.TitleCell>{translate('pim_common.status')}</Styled.TitleCell>
      <Styled.CellInputContainer>
        <Styled.InputContainer>
          <TextInput value={translate('pim_common.operators.=')} readOnly={true} />
        </Styled.InputContainer>
      </Styled.CellInputContainer>
      <Table.Cell>
        <Styled.InputContainer>
          <SelectInput
            value={condition.value ? 'true' : 'false'}
            emptyResultLabel={''}
            openLabel={''}
            onChange={handleChange}
            clearable={false}
          >
            <SelectInput.Option value="true" title={translate('pim_common.enabled')}>
              {translate('pim_common.enabled')}
            </SelectInput.Option>
            <SelectInput.Option value="false" title={translate('pim_identifier_generator.selection.enabled.disabled')}>
              {translate('pim_identifier_generator.selection.enabled.disabled')}
            </SelectInput.Option>
          </SelectInput>
        </Styled.InputContainer>
      </Table.Cell>
    </Table.Row>
  );
};

export {EnabledLine};
