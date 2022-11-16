import styled from 'styled-components';
import {getColor, Table} from 'akeneo-design-system';

const FormContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-top: 20px;
  margin-bottom: 20px;
`;

const FullPageCenteredContent = styled.div`
  display: flex;
  align-items: center;
  flex-direction: column;
  justify-content: center;
  height: 100vh;
  & svg {
    width: 500px;
  }
`;

const TitleCell = styled(Table.Cell)`
  font-style: italic;
  color: ${getColor('brand', 100)};
`;

const TwoColumns = styled.div`
  margin-top: 10px;
  display: grid;
  grid-template-columns: auto 300px;
  grid-template-rows: 1fr;
  grid-column-gap: 30px;
  & > * > * {
    margin-top: 10px;
  }
`;

const Styled = {
  FormContainer,
  FullPageCenteredContent,
  TitleCell,
  TwoColumns,
};

export {Styled};
