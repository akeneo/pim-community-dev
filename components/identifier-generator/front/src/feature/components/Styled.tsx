import styled from 'styled-components';

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

const Styled = {
  FormContainer,
  FullPageCenteredContent,
};

export {Styled};
