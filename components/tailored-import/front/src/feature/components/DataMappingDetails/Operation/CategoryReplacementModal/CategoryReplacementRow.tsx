import {Helper, LoaderIcon, Table, TagInput} from "akeneo-design-system";
import React, {useState} from "react";
import {useTranslate, ValidationError, filterErrors} from "@akeneo-pim-community/shared";
import styled from "styled-components";
import {ReplacementValues} from "../../../../models";
import {ArrowRightIcon} from "akeneo-design-system";
import {useCategoryFetcher} from "../../../../hooks/useCategoryFetcher";

type CategoryTreeModel = {
  id: number;
  code: string;
  label: string;
  loading?: boolean;
  isOpen: boolean;
  children?: CategoryTreeModel[];
};


const TreeArrowIcon = styled(ArrowRightIcon)<{$isFolderOpen: boolean}>`
  transform: rotate(${({$isFolderOpen}) => ($isFolderOpen ? '90' : '0')}deg);
  transition: transform 0.2s ease-out;
  vertical-align: middle;
  cursor: pointer;
`;

const Field = styled.div`
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
`;

const CategoryCell = styled(Table.Cell) < {level: number} >`
  padding-left: ${({level}) => (level * 20)}px;
`;

type CategoryReplacementRowProps = {
  mapping: ReplacementValues;
  onMappingChange: (mapping: ReplacementValues) => void;
  tree: CategoryTreeModel;
  validationErrors: ValidationError[];
  level: number;
};

const CategoryReplacementRow = ({
  mapping,
  onMappingChange,
  tree,
  validationErrors,
  level,
}: CategoryReplacementRowProps) => {
  const translate = useTranslate();
  const [categoryState, setCategoryState] = useState<CategoryTreeModel>(tree);
  const valueErrors = filterErrors(validationErrors, `[${tree.code}]`);
  const categoryFetcher = useCategoryFetcher();

  const handleMappingChange = (categoryTreeCode: string, newValues: string[]) => {
    onMappingChange({...mapping, [categoryTreeCode]: newValues});
  }

  const handleOpenCategory = async () => {
    if (categoryState.isOpen) {
      setCategoryState((categoryState) => ({...categoryState, isOpen: false}));
    } else {
      setCategoryState((categoryState) => ({...categoryState, isOpen: true, loading: true}));
      // load category
      const children = await categoryFetcher(tree.id);
      const categoryTreeModels = children.map((child) => ({
          code: child.code,
          children: [],
          label: child.label,
          loading: false,
          id: child.id,
          isOpen: false,
      }));

      setCategoryState((categoryState) => ({
          ...categoryState, loading: false, children: categoryTreeModels
        })
      );
    }
  }

  return (
    <>
      <Table.Row>
        <CategoryCell level={level} onClick={handleOpenCategory}>
          <TreeArrowIcon size={14} $isFolderOpen={categoryState.isOpen} />
          {categoryState.loading && (
            <LoaderIcon />
          )}
          {categoryState.label}
        </CategoryCell>
        <Table.Cell>
          <Field>
            <TagInput
              invalid={0 < valueErrors.length}
              separators={[',', ';']}
              placeholder={translate(
                'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
              )}
              value={mapping[categoryState.code] ?? []}
              onChange={newValue => handleMappingChange(tree.code, newValue)}
            />
            {valueErrors.map((error, index) => (
              <Helper key={index} inline={true} level="error">
                {translate(error.messageTemplate, error.parameters)}
              </Helper>
            ))}
          </Field>
        </Table.Cell>
      </Table.Row>
      {categoryState.isOpen && categoryState.children && categoryState.children.map((child) => (
        <CategoryReplacementRow
          key={child.id}
          tree={child}
          mapping={mapping}
          validationErrors={validationErrors}
          onMappingChange={onMappingChange}
          level={level + 1}
        />
      ))}
    </>
  )
}

export {CategoryReplacementRow};
