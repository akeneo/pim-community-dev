import React, {FunctionComponent, useState} from 'react';
import styled from 'styled-components';
import {
  ArrowDownIcon,
  BlockButton,
  Dropdown,
  getColor,
  Helper,
  IconButton,
  placeholderStyle,
  Preview,
  RefreshIcon,
  SectionTitle,
  SettingsIllustration,
  useBooleanState,
} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';
import {DataMapping, getDefaultOperation, Operation, OperationType} from '../../models';
import {CleanHTMLTagsOperationBlock, OperationBlockProps, CLEAN_HTML_TAGS_TYPE} from './Operation';

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

const PreviewContent = styled.div<{isLoading: boolean}>`
  overflow: hidden;
  max-width: 100ch;
  text-overflow: ellipsis;
  white-space: nowrap;

  ${({isLoading}) => isLoading && placeholderStyle}
`;

const EmptyPreviewContent = styled(PreviewContent)`
  color: ${getColor('grey', 100)};

  ${({isLoading}) => isLoading && placeholderStyle}
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
          <Preview title={translate('akeneo.tailored_import.data_mapping.preview.title')}>
            {dataMapping.sample_data.map((sampleData, key) => (
              <Preview.Row
                key={key}
                action={
                  <IconButton
                    disabled={loadingSampleData.includes(key)}
                    icon={<RefreshIcon />}
                    onClick={() => handleRefreshSampleData(key)}
                    title={translate('akeneo.tailored_import.data_mapping.preview.refresh')}
                  />
                }
              >
                {null !== sampleData ? (
                  <PreviewContent isLoading={loadingSampleData.includes(key)}>{sampleData}</PreviewContent>
                ) : (
                  <EmptyPreviewContent isLoading={loadingSampleData.includes(key)}>
                    {translate('akeneo.tailored_import.data_mapping.preview.placeholder')}
                  </EmptyPreviewContent>
                )}
              </Preview.Row>
            ))}
          </Preview>
          {dataMapping.operations.map(operation => {
            const OperationBlock = operationBlocks[operation.type] ?? null;

            if (null === OperationBlock) {
              console.error(`No operation block found for operation type "${operation.type}"`);

              return null;
            }

            return <OperationBlock key={operation.type} operation={operation} onRemove={handleOperationRemove} />;
          })}
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
