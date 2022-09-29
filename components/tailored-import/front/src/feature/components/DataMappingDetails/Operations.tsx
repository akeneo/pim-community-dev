import React, {FunctionComponent, useState} from 'react';
import styled from 'styled-components';
import {
  ArrowDownIcon,
  BlockButton,
  Dropdown,
  Helper,
  Link,
  SectionTitle,
  SettingsIllustration,
  useBooleanState,
} from 'akeneo-design-system';
import {filterErrors, useTranslate, ValidationError} from '@akeneo-pim-community/shared';
import {DataMapping, getDefaultOperation, Operation, OperationType, isAttributeTarget} from '../../models';
import {
  BOOLEAN_REPLACEMENT_OPERATION_TYPE,
  CATEGORIES_REPLACEMENT_OPERATION_TYPE,
  CLEAN_HTML_OPERATION_TYPE,
  CHANGE_CASE_OPERATION_TYPE,
  ENABLED_REPLACEMENT_OPERATION_TYPE,
  FAMILY_REPLACEMENT_OPERATION_TYPE,
  MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  REMOVE_WHITESPACE_OPERATION_TYPE,
  SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SPLIT_OPERATION_TYPE,
  BooleanReplacementOperationBlock,
  CategoriesReplacementOperationBlock,
  CleanHTMLOperationBlock,
  ChangeCaseOperationBlock,
  EnabledReplacementOperationBlock,
  FamilyReplacementOperationBlock,
  MultiReferenceEntityReplacementOperationBlock,
  MultiSelectReplacementOperationBlock,
  OperationBlockProps,
  OperationSampleData,
  RemoveWhitespaceOperationBlock,
  SimpleReferenceEntityReplacementOperationBlock,
  SimpleSelectReplacementOperationBlock,
  SplitOperationBlock,
} from './Operation';
import {usePreviewData} from '../../hooks';

const OperationsContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const OperationBlocksContainer = styled.div`
  display: flex;
  flex-direction: column;
  margin-top: 10px;
  gap: 10px;
`;

const operationBlocks: {
  [operationType in OperationType]: FunctionComponent<OperationBlockProps>;
} = {
  [BOOLEAN_REPLACEMENT_OPERATION_TYPE]: BooleanReplacementOperationBlock,
  [CATEGORIES_REPLACEMENT_OPERATION_TYPE]: CategoriesReplacementOperationBlock,
  [CHANGE_CASE_OPERATION_TYPE]: ChangeCaseOperationBlock,
  [CLEAN_HTML_OPERATION_TYPE]: CleanHTMLOperationBlock,
  [ENABLED_REPLACEMENT_OPERATION_TYPE]: EnabledReplacementOperationBlock,
  [FAMILY_REPLACEMENT_OPERATION_TYPE]: FamilyReplacementOperationBlock,
  [MULTI_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE]: MultiReferenceEntityReplacementOperationBlock,
  [MULTI_SELECT_REPLACEMENT_OPERATION_TYPE]: MultiSelectReplacementOperationBlock,
  [REMOVE_WHITESPACE_OPERATION_TYPE]: RemoveWhitespaceOperationBlock,
  [SIMPLE_REFERENCE_ENTITY_REPLACEMENT_OPERATION_TYPE]: SimpleReferenceEntityReplacementOperationBlock,
  [SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE]: SimpleSelectReplacementOperationBlock,
  [SPLIT_OPERATION_TYPE]: SplitOperationBlock,
};

type OperationsProps = {
  dataMapping: DataMapping;
  compatibleOperations: OperationType[];
  onOperationsChange: (operations: Operation[]) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
  validationErrors: ValidationError[];
};

const Operations = ({
  dataMapping,
  compatibleOperations,
  onOperationsChange,
  onRefreshSampleData,
  validationErrors,
}: OperationsProps) => {
  const translate = useTranslate();
  const [loadingSampleData, setLoadingSampleData] = useState<number[]>([]);
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const [previewDataIsLoading, previewData, previewDataHasError] = usePreviewData(dataMapping);

  const handleRefreshSampleData = async (indexToRefresh: number) => {
    setLoadingSampleData(loadingSampleData => [...loadingSampleData, indexToRefresh]);
    await onRefreshSampleData(indexToRefresh);
    setLoadingSampleData(loadingSampleData => loadingSampleData.filter(value => indexToRefresh !== value));
  };

  const handleOperationAdd = (operation: Operation) => {
    closeDropdown();
    onOperationsChange([...dataMapping.operations, operation]);
  };

  const handleOperationChange = (operation: Operation) => {
    onOperationsChange(dataMapping.operations.map(value => (value.type === operation.type ? operation : value)));
  };

  const handleOperationRemove = (operationType: OperationType) => {
    onOperationsChange(dataMapping.operations.filter(({type}) => type !== operationType));
  };

  const availableOperations = compatibleOperations.filter(
    operationType => !dataMapping.operations.find(({type}) => type === operationType)
  );

  return (
    <OperationsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.operations.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {0 === dataMapping.sources.length ? (
        <Helper level="info">{translate('akeneo.tailored_import.data_mapping.operations.no_source')}</Helper>
      ) : (
        <OperationBlocksContainer>
          <OperationSampleData
            sampleData={dataMapping.sample_data}
            onRefreshSampleData={handleRefreshSampleData}
            loadingSampleData={loadingSampleData}
          />
          {dataMapping.operations.map((operation, index) => {
            const OperationBlock = operationBlocks[operation.type] ?? null;

            if (null === OperationBlock) {
              console.error(`No operation block found for operation type "${operation.type}"`);

              return null;
            }

            return (
              <OperationBlock
                key={operation.type}
                targetReferenceDataName={
                  isAttributeTarget(dataMapping.target) ? dataMapping.target.reference_data_name : undefined
                }
                targetCode={dataMapping.target.code}
                operation={operation}
                previewData={{
                  data: previewData,
                  isLoading: previewDataIsLoading,
                  hasError: previewDataHasError,
                }}
                isLastOperation={index === dataMapping.operations.length - 1}
                onChange={handleOperationChange}
                onRemove={handleOperationRemove}
                validationErrors={filterErrors(validationErrors, `[${operation.uuid}]`)}
              />
            );
          })}
          <Dropdown>
            {0 < availableOperations.length ? (
              <BlockButton onClick={openDropdown} icon={<ArrowDownIcon />}>
                {translate('akeneo.tailored_import.data_mapping.operations.add')}
              </BlockButton>
            ) : (
              <Helper inline={true}>
                {translate('akeneo.tailored_import.data_mapping.operations.no_available.text')}{' '}
                <Link
                  href="https://help.akeneo.com/pim/serenity/articles/tailored-import.html#discover-operations"
                  target="_blank"
                >
                  {translate('akeneo.tailored_import.data_mapping.operations.no_available.link')}
                </Link>
              </Helper>
            )}
            {isDropdownOpen && (
              <Dropdown.Overlay dropdownOpenerVisible={true} onClose={closeDropdown} fullWidth={true}>
                <Dropdown.ItemCollection
                  noResultTitle={translate('akeneo.tailored_import.data_mapping.operations.no_result')}
                  noResultIllustration={<SettingsIllustration />}
                >
                  {availableOperations.map(operationType => (
                    <Dropdown.Item
                      key={operationType}
                      onClick={() => handleOperationAdd(getDefaultOperation(operationType))}
                    >
                      {translate(`akeneo.tailored_import.data_mapping.operations.${operationType}.title`)}
                    </Dropdown.Item>
                  ))}
                </Dropdown.ItemCollection>
              </Dropdown.Overlay>
            )}
          </Dropdown>
        </OperationBlocksContainer>
      )}
    </OperationsContainer>
  );
};

export {Operations};
export type {OperationsProps};
