import React from 'react';
import {Badge, LoaderIcon, TableInput} from 'akeneo-design-system';
import {getLabel, useTranslate, useUserContext} from '@akeneo-pim-community/shared';
import {diffChars} from 'diff';
import styled from 'styled-components';
import {ColumnCode, ColumnDefinition} from "../models/TableConfiguration";
import {TableRowWithId, TableValueWithId} from "../product/TableFieldApp";
import {useFetchOptions} from "../product/useFetchOptions";
import {TableAttribute} from "../models/Attribute";
// eslint-disable-next-line @typescript-eslint/no-var-requires
const FetcherRegistry = require('pim/fetcher-registry');

const StretchHeaderCell = styled(TableInput.HeaderCell)`
  min-width: auto;
`;

const StretchedBodyCell = styled(TableInput.Cell)`
  min-width: auto;
`;

type ProposalDiffTableProps = {
  accessor: 'before' | 'after';
  change: {
    attributeCode: string;
    before: TableValueWithId | null;
    after: TableValueWithId | null;
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

const ProposalDiffTable: React.FC<ProposalDiffTableProps> = ({accessor, change, ...rest}) => {
  const translate = useTranslate();
  const userContext = useUserContext();
  const valueData = change[accessor] || [];
  const catalogLocale = userContext.get('catalogLocale');
  const [attribute, setAttribute] = React.useState<TableAttribute | undefined>();
  const {getOptionLabel} = useFetchOptions(attribute?.table_configuration, change.attributeCode, valueData);

  React.useEffect(() => {
    FetcherRegistry.initialize().then(() => {
      FetcherRegistry.getFetcher('attribute')
        .fetch(change.attributeCode)
        .then((attribute: TableAttribute) => {
          setAttribute(attribute);
        });
    });
  }, []);

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
      beforeCell = getOptionLabel(columnCode, beforeCell) || '';
      afterCell = getOptionLabel(columnCode, afterCell) || '';
    }
    if (dataType === 'boolean') {
      const value = accessor === 'before' ? beforeCell : afterCell;

      if (value === true) {
        return <Badge level="primary">{translate('pim_common.yes')}</Badge>;
      }
      if (value === false) {
        return <Badge level="tertiary">{translate('pim_common.no')}</Badge>;
      }
    }
    if (displayChanges) return displayChange((beforeCell || '') + '', (afterCell || '') + '', accessor);
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
                  highlighted={accessor === 'after' && hasOrderChanged(row[firstColumnCode] as string)}
                >
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
