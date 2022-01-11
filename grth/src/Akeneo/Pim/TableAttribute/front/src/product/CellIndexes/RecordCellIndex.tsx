import React from 'react';
import {RecordCode, ReferenceEntityColumnDefinition, ReferenceEntityRecord} from '../../models';
import {getLabel, useRouter, useTranslate} from '@akeneo-pim-community/shared';
import {useAttributeContext, useLocaleCode} from '../../contexts';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {LoadingPlaceholderContainer} from '../../shared';
import styled from 'styled-components';
import {TableInput} from 'akeneo-design-system';
import {useCellMatchersMapping} from '../../contexts/CellMappingContext';

const FirstCellLoadingPlaceholderContainer = styled(LoadingPlaceholderContainer)`
  padding-top: 10px;
  & > * {
    height: 20px;
  }
`;

type RecordCellIndexProps = {
  searchText: string;
  value: RecordCode;
};

const RecordCellIndex: React.FC<RecordCellIndexProps> = ({searchText, value}) => {
  const router = useRouter();
  const translate = useTranslate();
  const localeCode = useLocaleCode();
  const {attribute} = useAttributeContext();
  const firstColumn = attribute?.table_configuration?.[0];
  const [record, setRecord] = React.useState<ReferenceEntityRecord | undefined | null>();
  const cellMatchersMapping = useCellMatchersMapping();
  const isMatching = cellMatchersMapping['reference_entity']
    ? cellMatchersMapping['reference_entity'].default()
    : () => false;

  React.useEffect(() => {
    if (attribute) {
      ReferenceEntityRecordRepository.findByCode(
        router,
        (firstColumn as ReferenceEntityColumnDefinition).reference_entity_identifier,
        value
      ).then(record => {
        setRecord(record);
      });
    }
  }, [attribute, firstColumn, router, value]);

  return (
    <>
      {firstColumn && (
        <TableInput.CellContent
          rowTitle={true}
          highlighted={isMatching(value, searchText, firstColumn.code)}
          inError={record === null}
        >
          {typeof record === 'undefined' ? (
            <FirstCellLoadingPlaceholderContainer>
              <div>{translate('pim_common.loading')}</div>
            </FirstCellLoadingPlaceholderContainer>
          ) : (
            getLabel(record?.labels || {}, localeCode, value)
          )}
        </TableInput.CellContent>
      )}
    </>
  );
};

export {RecordCellIndex};
