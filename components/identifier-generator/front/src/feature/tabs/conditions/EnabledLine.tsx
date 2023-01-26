import React from 'react';
import {Condition, EnabledCondition} from '../../models';
import {Button, SelectInput, Table, TextInput} from 'akeneo-design-system';
import {Styled} from '../../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useIdentifierGeneratorAclContext} from '../../context';

type EnabledLineProps = {
  condition: EnabledCondition;
  onChange: (condition: Condition) => void;
  onDelete: () => void;
};

const EnabledLine: React.FC<EnabledLineProps> = ({condition, onChange, onDelete}) => {
  const translate = useTranslate();
  const identifierGeneratorAclContext = useIdentifierGeneratorAclContext();

  const handleChange = (value: string) => {
    onChange({...condition, value: value === 'true'});
  };

  return (
    <>
      <Styled.TitleCondition>{translate('pim_common.status')}</Styled.TitleCondition>
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
            readOnly={!identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted}
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
        {identifierGeneratorAclContext.isManageIdentifierGeneratorAclGranted && (
          <Button onClick={onDelete} ghost level="danger">
            {translate('pim_common.delete')}
          </Button>
        )}
      </Table.ActionCell>
    </>
  );
};

export {EnabledLine};
