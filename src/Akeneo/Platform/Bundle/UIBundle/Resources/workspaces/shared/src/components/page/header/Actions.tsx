import styled, {css} from 'styled-components';

type Props = {
  userActionVisible?: boolean;
};

const Actions = styled.div<Props>`
  display: flex;
  align-items: center;
  margin-right: -10px;

  > :not(:first-child) {
    margin-left: 10px;
  }

  ${props =>
    props.userActionVisible &&
    css`
      border-left: 1px solid ${({theme}) => theme.color.grey80};
      margin-left: 20px;
      padding-left: 20px;
    `}
`;

export {Actions};
