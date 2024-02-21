import styled, {css} from 'styled-components';

type Props = {
  isFilterable?: boolean;
};

const filterableMixin = css`
  position: sticky;
  top: 44px;

  &:hover {
    cursor: pointer;
  }
`;

const notFilterableMixin = css`
  position: sticky;
  top: 0;
`;

const TableHeadCell = styled.th<Props>`
  text-align: left;
  font-weight: normal;
  height: calc(44px + 20px);
  box-shadow: 0 1px 0 ${({theme}) => theme.color.grey120};
  background: ${({theme}) => theme.color.white};
  padding-top: 15px;
  z-index: 1;

  ${props => (props.isFilterable ? filterableMixin : notFilterableMixin)} {
    padding-left: 10px;
  }
`;

export {TableHeadCell};
