import styled, {css} from 'styled-components';

const Table = styled.table`
  width: 100%;
  color: ${props => props.theme.color.grey140};
  border-collapse: collapse;

  td {
    width: 25%;
  }
`;

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

const Row = styled.tr<{isSelected?: boolean}>`
  cursor: pointer;
  height: 54px;
  border-bottom: 1px solid ${props => props.theme.color.grey70};

  :hover {
    background-color: ${props => props.theme.color.grey60};
  }

  ${props =>
    props.isSelected &&
    css`
      background-color: ${props.theme.color.blue20};
    `}
`;

const HeaderCell = styled.th`
  text-align: left;
  font-weight: normal;
  position: sticky;
  top: 50px;
  height: 43px;
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background: ${props => props.theme.color.white};
`;

const LabelCell = styled.td`
  color: ${props => props.theme.color.purple100};
  font-style: italic;
  font-weight: bold;
`;

export {Table, TablePlaceholder, Row, HeaderCell, LabelCell};
