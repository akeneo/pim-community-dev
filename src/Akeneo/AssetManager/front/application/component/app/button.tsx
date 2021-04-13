import styled from 'styled-components';

const ButtonContainer = styled.div`
  display: flex;
  gap: 10px;
  align-items: center;
`;

const TransparentButton = styled.button`
  background: none;
  border: none;
  padding: 0;
  margin: 0;

  &:hover {
    cursor: pointer;
  }
`;

export {ButtonContainer, TransparentButton};
