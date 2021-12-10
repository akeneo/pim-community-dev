import React from 'react';
import {RecordCode, RecordColumnDefinition, ReferenceEntityRecord} from '../../models';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {useAttributeContext} from '../../contexts';
import {ReferenceEntityRecordRepository} from '../../repositories';
import {LoadingPlaceholderContainer} from '../../shared';
import styled from 'styled-components';
import {TableInput} from 'akeneo-design-system';
import {CellMatchersMapping} from '../CellMatchers';

const FirstCellLoadingPlaceholderContainer = styled(LoadingPlaceholderContainer)`
  padding-top: 10px;
  & > * {
    height: 20px;
  }
`;

type RecordCellIndexProps = {
  cellMatchersMapping: CellMatchersMapping;
  searchText: string;
  value: RecordCode;
};

const RecordCellIndex: React.FC<RecordCellIndexProps> = ({cellMatchersMapping, searchText, value}) => {
  const userContext = useUserContext();
  const router = useRouter();
  const translate = useTranslate();
  const catalogLocale = userContext.get('catalogLocale');
  const {attribute} = useAttributeContext();
  const firstColumn = attribute?.table_configuration?.[0];
  const [record, setRecord] = React.useState<ReferenceEntityRecord | undefined | null>();
  const isMatching = cellMatchersMapping['record'] ? cellMatchersMapping['record'].default() : () => false;

  React.useEffect(() => {
    if (attribute) {
      ReferenceEntityRecordRepository.findByCode(
        router,
        (firstColumn as RecordColumnDefinition).reference_entity_identifier,
        value
      ).then(record => {
        setRecord(record);
      });
    }
  }, [attribute]);

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
            getLabel(record?.labels || {}, catalogLocale, value)
          )}
        </TableInput.CellContent>
      )}
    </>
  );
};

export {RecordCellIndex};
