import styled from 'styled-components';
import {getColor, Table} from 'akeneo-design-system';

const TablePlaceholder = styled.div`
  display: grid;
  grid-row-gap: 10px;

  > div {
    height: 54px;
  }
`;

//TODO should this be in the DSM?
const HeaderCell = styled(Table.HeaderCell)`
  position: sticky;
  top: 44px;
`;

export {TablePlaceholder, HeaderCell};
