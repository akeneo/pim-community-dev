import React from 'react';
import styled from 'styled-components';
import {Table, TextInput, Helper, Checkbox} from 'akeneo-design-system';
import {ValidationError, useTranslate, filterErrors} from '@akeneo-pim-community/shared';
import {SearchAndReplaceValue} from './SearchAndReplaceOperationBlock';

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type SearchAndReplaceValueRowProps = {
  replacement: SearchAndReplaceValue;
  validationErrors: ValidationError[];
  onReplacementChange: (replacement: SearchAndReplaceValue) => void;
};

const SearchAndReplaceValueRow = ({
  replacement,
  validationErrors,
  onReplacementChange,
}: SearchAndReplaceValueRowProps) => {
  const translate = useTranslate();
  const whatValidationErrors = filterErrors(validationErrors, '[what]');
  const withValidationErrors = filterErrors(validationErrors, '[with]');

  return (
    <Table.Row>
      <Table.Cell>
        <Field>
          <TextInput
            value={replacement.what}
            invalid={0 < whatValidationErrors.length}
            placeholder={translate(
              'akeneo.tailored_import.data_mapping.operations.search_and_replace.what.placeholder'
            )}
            onChange={whatValue => onReplacementChange({...replacement, what: whatValue})}
          />
          {whatValidationErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Table.Cell>
      <Table.Cell>
        <Field>
          <TextInput
            value={replacement.with}
            invalid={0 < withValidationErrors.length}
            placeholder={translate(
              'akeneo.tailored_import.data_mapping.operations.search_and_replace.with.placeholder'
            )}
            onChange={withValue => onReplacementChange({...replacement, with: withValue})}
          />
          {withValidationErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Table.Cell>
      <Table.Cell>
        <Checkbox
          checked={replacement.case_sensitive}
          onChange={caseSensitive => onReplacementChange({...replacement, case_sensitive: caseSensitive})}
        />
      </Table.Cell>
    </Table.Row>
  );
};

export {SearchAndReplaceValueRow};
