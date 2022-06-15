import {Helper, Table, TagInput} from 'akeneo-design-system';
import React from 'react';
import {CategoryTree} from '../../../../models';
import {getLabel, useTranslate, useUserContext, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {ReplacementValues} from '../../../../models';
import styled from 'styled-components';

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

type CategoriesReplacementListProps = {
  categoryTree: CategoryTree;
  onMappingChange: (mapping: ReplacementValues) => void;
  mapping: ReplacementValues;
  validationErrors: ValidationError[];
};

const CategoriesReplacementList = ({
  categoryTree,
  onMappingChange,
  mapping,
  validationErrors,
}: CategoriesReplacementListProps) => {
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const valueErrors = filterErrors(validationErrors, `[${categoryTree.code}]`);

  const handleMappingChange = (categoryTreeCode: string, newValues: string[]) => {
    onMappingChange({...mapping, [categoryTreeCode]: newValues});
  };

  return (
    <Table.Row>
      <Table.Cell>{getLabel(categoryTree.labels, catalogLocale, categoryTree.code)}</Table.Cell>
      <Table.Cell>
        <Field>
          <TagInput
            invalid={0 < valueErrors.length}
            separators={[',', ';']}
            placeholder={translate(
              'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
            )}
            value={mapping[categoryTree.code] ?? []}
            onChange={newValue => handleMappingChange(categoryTree.code, newValue)}
          />
          {valueErrors.map((error, index) => (
            <Helper key={index} inline={true} level="error">
              {translate(error.messageTemplate, error.parameters)}
            </Helper>
          ))}
        </Field>
      </Table.Cell>
    </Table.Row>
  );
};

export {CategoriesReplacementList};
