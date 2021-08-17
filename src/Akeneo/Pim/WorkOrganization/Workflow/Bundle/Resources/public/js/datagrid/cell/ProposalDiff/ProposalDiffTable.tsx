import React from 'react';
import {ProposalChangeAccessor} from '../ProposalChange';
import {TableRowWithId, TableValueWithId} from "@akeneo-pim-ge/table_attribute/src/product/TableFieldApp";
import {TableAttribute} from "@akeneo-pim-ge/table_attribute/src/models/Attribute";
import {TableInput} from "akeneo-design-system";
import {getLabel} from "@akeneo-pim-community/shared";
import {ColumnCode, ColumnDefinition} from "@akeneo-pim-ge/table_attribute/src/models/TableConfiguration";
import {useFetchOptions} from "@akeneo-pim-ge/table_attribute/src/product/useFetchOptions";
import {diffChars} from "diff";
const UserContext = require('pim/user-context');

const FetcherRegistry = require('pim/fetcher-registry');

type ProposalDiffTableProps = {
  accessor: ProposalChangeAccessor;
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
  })
}

const ProposalDiffTable: React.FC<ProposalDiffTableProps> = ({accessor, change, ...rest}) => {
  const valueData = change[accessor] || [];
  const catalogLocale = UserContext.get('catalogLocale');
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
    return 'loading';
  }

  const tableConfiguration = attribute.table_configuration;
  const firstColumnCode = tableConfiguration[0].code;

  const hasOrderChanged = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder !== afterOrder;
  }

  const isRowAdded = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder < 0 && afterOrder >= 0;
  }

  const isRowDeleted = (optionCode: string) => {
    const beforeOrder = (change['before'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    const afterOrder = (change['after'] || []).findIndex(row => row[firstColumnCode] === optionCode);
    return beforeOrder >= 0 && afterOrder < 0;
  }

  const isCellAdded: (optionCode: string, columnCode: ColumnCode) => boolean = (optionCode, columnCode) => {
    const beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    const afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    return ((beforeCell || '') === '') && ((afterCell || '') !== '');
  }

  const isCellDeleted: (optionCode: string, columnCode: ColumnCode) => boolean = (optionCode, columnCode) => {
    const beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    const afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    return ((beforeCell || '') !== '') && ((afterCell || '') === '');
  }

  const displayCell = (optionCode: string, columnCode: ColumnCode, displayChanges: boolean) => {
    let beforeCell = ((change['before'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    let afterCell = ((change['after'] || []).find(row => row[firstColumnCode] === optionCode) || {} as TableRowWithId)[columnCode];
    if ((tableConfiguration.find(column => column.code === columnCode) as ColumnDefinition).data_type === 'select') {
      beforeCell = getOptionLabel(columnCode, beforeCell) || '';
      afterCell = getOptionLabel(columnCode, afterCell) || '';
    }
    if (displayChanges) return displayChange((beforeCell || '') + '', (afterCell || '') + '', accessor);
    return accessor === 'before' ? beforeCell : afterCell;
  }

  return (
    <span {...rest}>
      <TableInput>
        <TableInput.Header>
          <TableInput.HeaderCell>Order TODO</TableInput.HeaderCell>
          {tableConfiguration.map(column => <TableInput.HeaderCell key={column.code}>
            {getLabel(column.labels, catalogLocale, column.code)}
          </TableInput.HeaderCell>)}
        </TableInput.Header>
        <TableInput.Body>
          {valueData.map((row, i) => <TableInput.Row key={i}>
            <TableInput.Cell
              inError={accessor === 'before' && hasOrderChanged(row[firstColumnCode] as string)}
              highlighted={accessor === 'after' && hasOrderChanged(row[firstColumnCode] as string)}
            >
              {i + 1}
            </TableInput.Cell>
            {tableConfiguration.map((column, j) => {
              const isCellRed = accessor === 'before' && (isRowDeleted(row[firstColumnCode] as string) || isCellDeleted(row[firstColumnCode] as string, column.code));
              const isCellGreen = accessor === 'after' && (isRowAdded(row[firstColumnCode] as string) || isCellAdded(row[firstColumnCode] as string, column.code));

              return <TableInput.Cell
                rowTitle={j === 0}
                key={column.code}
                inError={isCellRed}
                highlighted={isCellGreen}
              >
                {displayCell(row[firstColumnCode] as string, column.code, !isCellRed && !isCellGreen)}
              </TableInput.Cell>
            })}
          </TableInput.Row>)}
        </TableInput.Body>
      </TableInput>
    </span>
  );
};

class ProposalDiffTableMatcher {
  static supports(attributeType: string) {
    return ['pim_catalog_table'].includes(attributeType);
  }

  static render() {
    return ProposalDiffTable;
  }
}

export {ProposalDiffTableMatcher};
