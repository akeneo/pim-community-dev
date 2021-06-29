import React, {CSSProperties, FC} from 'react';

const Table: FC = ({children, ...props}) => {
  return (
    <table className="AknGrid AknGrid--unclickable" {...props}>
      <tbody className="AknGrid-body">{children}</tbody>
    </table>
  );
};

type RowProps = {
  isHeader?: boolean;
};

const Row: FC<RowProps> = ({children, isHeader = false, ...props}) => {
  const rowClass = isHeader ? '' : 'AknGrid-bodyRow';

  return (
    <tr className={rowClass} {...props}>
      {children}
    </tr>
  );
};

type HeaderCellProps = {
  align?: 'left' | 'center' | 'right';
  width?: number | string;
};

const HeaderCell: FC<HeaderCellProps> = ({children, align = 'left', width, ...props}) => {
  const style: CSSProperties = {
    textAlign: align,
    width: width,
  };

  return (
    <th className={'AknGrid-headerCell'} style={style} {...props}>
      {children}
    </th>
  );
};

type CellProps = {
  align?: 'left' | 'center' | 'right';
  action?: boolean;
  highlight?: boolean;
};

const Cell: FC<CellProps> = ({children, align = 'left', action = false, highlight = false, ...props}) => {
  const actionClass: string = action ? 'AknGrid-bodyCell--actions' : '';
  const highlightClass: string = highlight ? 'AknGrid-bodyCell--highlight' : '';
  const style: CSSProperties = {
    textAlign: align,
  };

  return (
    <td className={`AknGrid-bodyCell ${actionClass} ${highlightClass}`} style={style} {...props}>
      {children}
    </td>
  );
};

export {Table, Row, HeaderCell, Cell};
