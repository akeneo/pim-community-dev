import React, {useCallback, useEffect, useState} from 'react';
import styled from 'styled-components';
import {Helper, LoaderIcon, Table, TagInput, ArrowRightIcon} from 'akeneo-design-system';
import {useTranslate, ValidationError, filterErrors} from '@akeneo-pim-community/shared';
import {Category, ReplacementValues} from '../../../../models';
import {useCategoryFetcher} from '../../../../hooks';

type CategoryState = {
  loading?: boolean;
  isOpen: boolean;
  children: Category[];
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
  gap: 10px;
`;

const CategoryCell = styled(Table.Cell)<{level: number; isLeaf: boolean}>`
  padding-left: ${({level}) => level * 20}px;
  cursor: ${({isLeaf}) => (isLeaf ? 'default' : 'pointer')};
`;

const InnerCategoryCell = styled.div`
  display: flex;
  flex-direction: row;
  gap: 5px;
  align-items: center;
`;

type CategoryReplacementRowProps = {
  mapping: ReplacementValues;
  onMappingChange: (mapping: ReplacementValues) => void;
  tree: Category;
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
  const [categoryState, setCategoryState] = useState<CategoryState>({
    loading: false,
    isOpen: false,
    children: [],
  });
  const valueErrors = filterErrors(validationErrors, `[${tree.code}]`);
  const categoryFetcher = useCategoryFetcher();

  const handleMappingChange = (categoryTreeCode: string, newValues: string[]) => {
    onMappingChange({...mapping, [categoryTreeCode]: newValues});
  };

  const handleOpenCategory = useCallback(async () => {
    if (categoryState.children.length > 0) {
      setCategoryState(categoryState => ({...categoryState, isOpen: true}));
    } else {
      setCategoryState(categoryState => ({...categoryState, isOpen: true, loading: true}));
      const children = await categoryFetcher(tree.id);
      setCategoryState(categoryState => ({
        ...categoryState,
        loading: false,
        children,
      }));
    }
  }, [categoryState.children, tree, categoryFetcher]);

  const handleToggleCategory = async () => {
    if (categoryState.isOpen) {
      setCategoryState(categoryState => ({...categoryState, isOpen: false}));
    } else {
      await handleOpenCategory();
    }
  };

  useEffect(() => {
    if (level === 0) {
      void handleOpenCategory();
    }
  }, [tree.id, level, handleOpenCategory]);

  return (
    <>
      <Table.Row>
        <CategoryCell level={level} isLeaf={tree.isLeaf} onClick={tree.isLeaf ? undefined : handleToggleCategory}>
          <InnerCategoryCell>
            <TreeArrowIcon
              color={tree.isLeaf ? 'transparent' : 'currentColor'}
              size={14}
              $isFolderOpen={categoryState.isOpen}
            />
            {categoryState.loading && <LoaderIcon />}
            {tree.label}
          </InnerCategoryCell>
        </CategoryCell>
        <Table.Cell width="40%">
          <Field>
            <TagInput
              invalid={0 < valueErrors.length}
              separators={[',', ';']}
              placeholder={translate(
                'akeneo.tailored_import.data_mapping.operations.replacement.modal.table.field.to_placeholder'
              )}
              value={mapping[tree.code] ?? []}
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
      {categoryState.isOpen &&
        categoryState.children.map(child => (
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
  );
};

export {CategoryReplacementRow};
