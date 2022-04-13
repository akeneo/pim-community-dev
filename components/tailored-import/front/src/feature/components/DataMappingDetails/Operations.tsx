import React, {FunctionComponent, useState} from 'react';
import styled from 'styled-components';
import {
  ArrowDownIcon,
  BlockButton,
  Dropdown,
  Helper,
  SectionTitle,
  SettingsIllustration,
  useBooleanState,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DataMapping, getDefaultOperation, Operation, OperationType} from '../../models';
import {CleanHTMLTagsOperationBlock, OperationBlockProps, OperationPreviewData, OperationSampleData, CLEAN_HTML_TAGS_TYPE} from './Operation';
import {usePreviewData} from "../../hooks/usePreviewData";

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
  [CLEAN_HTML_TAGS_TYPE]: CleanHTMLTagsOperationBlock,
};

type OperationsProps = {
  dataMapping: DataMapping;
  compatibleOperations: OperationType[];
  onOperationsChange: (operations: Operation[]) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
};

const Operations = ({dataMapping, compatibleOperations, onOperationsChange, onRefreshSampleData}: OperationsProps) => {
  const translate = useTranslate();
  const [loadingSampleData, setLoadingSampleData] = useState<number[]>([]);
  const [isDropdownOpen, openDropdown, closeDropdown] = useBooleanState();
  const [previewDataIsLoading, previewData, previewDataValidationErrors] = usePreviewData(dataMapping);

  const handleRefreshSampleData = async (indexToRefresh: number) => {
    setLoadingSampleData(loadingSampleData => [...loadingSampleData, indexToRefresh]);
    await onRefreshSampleData(indexToRefresh);
    setLoadingSampleData(loadingSampleData => loadingSampleData.filter(value => indexToRefresh !== value));
  };

  const handleOperationAdd = (operation: Operation) => {
    closeDropdown();
    onOperationsChange([...dataMapping.operations, operation]);
  };

  const handleOperationRemove = (operationType: OperationType) => {
    onOperationsChange(dataMapping.operations.filter(({type}) => type !== operationType));
  };

  return (
    <OperationsContainer>
      <SectionTitle sticky={0}>
        <SectionTitle.Title level="secondary">
          {translate('akeneo.tailored_import.data_mapping.operations.title')}
        </SectionTitle.Title>
      </SectionTitle>
      {0 === dataMapping.sources.length ? (
        <Helper>{translate('akeneo.tailored_import.data_mapping.operations.no_source')}</Helper>
      ) : (
        <OperationBlocksContainer>
          <OperationSampleData
            sampleData={dataMapping.sample_data}
            onRefreshSampleData={handleRefreshSampleData}
            loadingSampleData={loadingSampleData}
          />
          {dataMapping.operations.map(operation => {
            const OperationBlock = operationBlocks[operation.type] ?? null;

            if (null === OperationBlock) {
              console.error(`No operation block found for operation type "${operation.type}"`);

              return null;
            }

            return <OperationBlock key={operation.type} operation={operation} onRemove={handleOperationRemove} />;
          })}
          {dataMapping.operations.length > 0 && (
            <OperationPreviewData isLoading={previewDataIsLoading} previewData={previewData} validationErrors={previewDataValidationErrors} />
          )}
          <Dropdown>
            <BlockButton onClick={openDropdown} icon={<ArrowDownIcon />}>
              {translate('akeneo.tailored_import.data_mapping.operations.add')}
            </BlockButton>
            {isDropdownOpen && (
              <Dropdown.Overlay onClose={closeDropdown} fullWidth={true}>
                <Dropdown.ItemCollection
                  noResultTitle={translate('akeneo.tailored_import.data_mapping.operations.no_result')}
                  noResultIllustration={<SettingsIllustration />}
                >
                  {compatibleOperations
                    .filter(operationType => !dataMapping.operations.find(({type}) => type === operationType))
                    .map(operationType => (
                      <Dropdown.Item
                        key={operationType}
                        onClick={() => handleOperationAdd(getDefaultOperation(operationType))}
                      >
                        {translate(`akeneo.tailored_import.data_mapping.operations.${operationType}`)}
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
