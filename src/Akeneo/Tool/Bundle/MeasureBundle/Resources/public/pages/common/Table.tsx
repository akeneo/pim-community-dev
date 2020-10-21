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
  border-bottom: 1px solid ${props => props.theme.color.grey80};

  :hover {
    background-color: ${props => props.theme.color.blue20};
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
  top: 44px;
  height: calc(44px + 15px);
  box-shadow: 0 1px 0 ${props => props.theme.color.grey120};
  background: ${props => props.theme.color.white};
  padding-top: 15px;

  :first-child {
    padding-left: 20px;
  }
`;

const LabelCell = styled.td`
  color: ${props => props.theme.color.purple100};
  font-style: italic;
  font-weight: bold;
  padding-left: 20px;
`;

export {Table, TablePlaceholder, Row, HeaderCell, LabelCell};
