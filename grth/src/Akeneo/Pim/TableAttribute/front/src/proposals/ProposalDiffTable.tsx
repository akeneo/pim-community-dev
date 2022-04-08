import React from 'react';
import {Badge, LoaderIcon, TableInput} from 'akeneo-design-system';
import {getLabel, useRouter, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {diffChars} from 'diff';
import styled from 'styled-components';
import {
  AttributeCode,
  castReferenceEntityColumnDefinition,
  ColumnCode,
  ColumnDefinition,
  RecordCode,
  ReferenceEntityColumnDefinition,
  ReferenceEntityIdentifierOrCode,
  TableAttribute,
  TableValue,
} from '../models';
import {TableRowWithId, useFetchOptions} from '../product';
import {AttributeRepository, ReferenceEntityRecordRepository} from '../repositories';
import {LocaleCodeContext} from '../contexts';

const StretchHeaderCell = styled(TableInput.HeaderCell)`
  min-width: auto;
`;

const StretchedBodyCell = styled(TableInput.Cell)`
  min-width: auto;
`;

type ProposalDiffTableProps = {
  accessor: 'before' | 'after';
  change: {
    attributeCode: AttributeCode;
    before: TableValue | null;
    after: TableValue | null;
  };
};

const displayChange = (before: string, after: string, accessor: 'before' | 'after') => {
  const changes = diffChars(before, after);

  return changes.map((change, i) => {
    if (accessor === 'before' && change.removed) {
      return <del key={i}>{change.value}</del>;
    }
    if (accessor === 'after' && change.added) {
      return <ins key={i}>{change.value}</ins>;
    }
    if ((accessor === 'before' && !change.added) || (accessor === 'after' && !change.removed)) {
      return change.value;
    }
    return null;
  });
};

const ProposalDiffTable: React.FC<ProposalDiffTableProps> = props => {
  const userContext = useUserContext();
  const catalogLocale = userContext.get('catalogLocale');

  return (
    <LocaleCodeContext.Provider value={{localeCode: catalogLocale}}>
      <ProposalDiffTableInner {...props} />
    </LocaleCodeContext.Provider>
  );
};

const ProposalDiffTableInner: React.FC<ProposalDiffTableProps> = ({accessor, change, ...rest}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const router = useRouter();
  const valueData = change[accessor] || [];
  const catalogLocale = userContext.get('catalogLocale');
  const catalogChannel = userContext.get('catalogScope');
  const [attribute, setAttribute] = React.useState<TableAttribute | undefined>();
  const {getOptionLabel} = useFetchOptions(attribute, setAttribute);
  const [isLoaded, setIsLoaded] = React.useState<boolean>(false);

  React.useEffect(() => {
    AttributeRepository.find(router, change.attributeCode).then(attribute => setAttribute(attribute as TableAttribute));
  }, []);

  React.useEffect(() => {
    if (!attribute) return;
    const recordCodesPerReferenceEntityCode: {[referenceEntityCode: string]: RecordCode[]} = {};
    const referenceEntityColumns = attribute.table_configuration.filter(
      ({data_type}) => data_type === 'reference_entity'
    ) as ReferenceEntityColumnDefinition[];
    const referenceEntityColumnCodes = referenceEntityColumns.map(({code}) => code);

    [change.before, change.after].forEach(change =>
      change?.forEach(row => {
        referenceEntityColumnCodes.forEach(columnCode => {
          if (row[columnCode]) {
            const recordCode = row[columnCode] as RecordCode;
            const referenceEntityCode = referenceEntityColumns.find(({code}) => code === columnCode)
              ?.reference_entity_identifier as ReferenceEntityIdentifierOrCode;
            if (!recordCodesPerReferenceEntityCode[referenceEntityCode])
              recordCodesPerReferenceEntityCode[referenceEntityCode] = [];
            recordCodesPerReferenceEntityCode[referenceEntityCode].push(recordCode);
          }
        });
      })
    );
    const promises: Promise<any>[] = [];
    Object.entries(recordCodesPerReferenceEntityCode).forEach(([referenceEntityCode, codes]) => {
      promises.push(
        ReferenceEntityRecordRepository.search(router, referenceEntityCode, {
          channel: catalogChannel,
          locale: catalogLocale,
          codes: Array.from(new Set(codes)),
        })
      );
    });

    Promise.all(promises).then(() => setIsLoaded(true));
  }, [attribute]);

  const getRecordLabel = (columnCode: string, recordCode?: RecordCode) => {
    if (!isLoaded || !recordCode) return '';
    const column = attribute?.table_configuration.find(({code}) => code === columnCode);
    const referenceEntityIdentifier = column
      ? castReferenceEntityColumnDefinition(column).reference_entity_identifier
      : '';
    const record = ReferenceEntityRecordRepository.getCachedByCode(referenceEntityIdentifier, recordCode);
    return getLabel(record?.labels || {}, catalogLocale, recordCode);
  };

  if (typeof attribute === 'undefined') {
    return <LoaderIcon />;
  }

  const tableConfiguration = attribute.table_configuration;
  const firstColumnCode = tableConfiguration[0].code;

  const hasOrderChanged = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder !== afterOrder;
  };

  const isRowAdded = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder < 0 && afterOrder >= 0;
  };

  const isRowDeleted = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder >= 0 && afterOrder < 0;
  };

  const isCellAdded: (optionCode: string, columnCode: ColumnCode) => boolean = (optionCode, columnCode) => {
    const beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    const afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    return typeof beforeCell === 'undefined' && typeof afterCell !== 'undefined';
  };

  const isCellDeleted: (optionCode: string, columnCode: ColumnCode) => boolean = (optionCode, columnCode) => {
    const beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    const afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    return typeof beforeCell !== 'undefined' && typeof afterCell === 'undefined';
  };

  const getCellContent = (optionCode: string, columnCode: ColumnCode, displayChanges: boolean) => {
    let beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    let afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) ||
      ({} as TableRowWithId))[columnCode];
    const dataType = (tableConfiguration.find(column => column.code === columnCode) as ColumnDefinition).data_type;
    if (dataType === 'select') {
      beforeCell = getOptionLabel(columnCode, beforeCell as string) || '';
      afterCell = getOptionLabel(columnCode, afterCell as string) || '';
    }
    if (dataType === 'reference_entity') {
      beforeCell = getRecordLabel(columnCode, beforeCell as RecordCode) || '';
      afterCell = getRecordLabel(columnCode, afterCell as RecordCode) || '';
    }
    if (dataType === 'boolean') {
      const value = accessor === 'before' ? beforeCell : afterCell;

      if (value === true) {
        return <Badge level='primary'>{translate('pim_common.yes')}</Badge>;
      }
      if (value === false) {
        return <Badge level='tertiary'>{translate('pim_common.no')}</Badge>;
      }
    }
    if (typeof beforeCell === 'number') {
      beforeCell = beforeCell.toString();
    }
    if (typeof afterCell === 'number') {
      afterCell = afterCell.toString();
    }
    if (displayChanges)
      return displayChange(((beforeCell as string) || '') + '', ((afterCell as string) || '') + '', accessor);
    return accessor === 'before' ? beforeCell : afterCell;
  };

  return (
    <span {...rest}>
      <TableInput>
        <TableInput.Header>
          <StretchHeaderCell>{translate('pim_table_attribute.form.product.order')}</StretchHeaderCell>
          {tableConfiguration.map(column => (
            <TableInput.HeaderCell key={column.code}>
              {getLabel(column.labels, catalogLocale, column.code)}
            </TableInput.HeaderCell>
          ))}
        </TableInput.Header>
        <TableInput.Body>
          {valueData.map((row, i) => (
            <TableInput.Row key={i}>
              <StretchedBodyCell>
                <TableInput.CellContent
                  inError={accessor === 'before' && hasOrderChanged(row[firstColumnCode] as string)}
                  highlighted={accessor === 'after' && hasOrderChanged(row[firstColumnCode] as string)}>
                  {i + 1}
                </TableInput.CellContent>
              </StretchedBodyCell>
              {tableConfiguration.map((column, j) => {
                const isCellRed =
                  accessor === 'before' &&
                  (isRowDeleted(row[firstColumnCode] as string) ||
                    isCellDeleted(row[firstColumnCode] as string, column.code));
                const isCellGreen =
                  accessor === 'after' &&
                  (isRowAdded(row[firstColumnCode] as string) ||
                    isCellAdded(row[firstColumnCode] as string, column.code));

                return (
                  <TableInput.Cell key={column.code}>
                    <TableInput.CellContent rowTitle={j === 0} inError={isCellRed} highlighted={isCellGreen}>
                      {getCellContent(row[firstColumnCode] as string, column.code, !isCellRed && !isCellGreen)}
                    </TableInput.CellContent>
                  </TableInput.Cell>
                );
              })}
            </TableInput.Row>
          ))}
        </TableInput.Body>
      </TableInput>
    </span>
  );
};

export default ProposalDiffTable;
