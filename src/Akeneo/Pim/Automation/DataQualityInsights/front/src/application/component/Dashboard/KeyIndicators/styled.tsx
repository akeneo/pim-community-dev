import styled from 'styled-components';

export const Icon = styled.div`
  border-right: 1px solid ${({theme}) => theme.color.grey80};
  min-width: 64px;
  padding-top: 18px;
  height: 60px;
  text-align: center;
  margin-right: 20px;
  color: ${({theme}) => theme.color.grey100};
`;

export const TextWithLink = styled.span`
  a,
  button {
    color: ${({theme}) => theme.color.blue100};
    text-decoration: underline ${({theme}) => theme.color.blue100};
    cursor: pointer;
    border: none;
    background: none;
    padding: 0;
    margin: 0;

    :focus {
      outline: none;
    }
  }
`;

export const Container = styled.div`
  flex: 1 0 50%;
  display: flex;
  margin: 24px 0 0 0;
  max-width: 50%;

  :nth-child(odd) {
    padding-right: 20px;
  }
  :nth-child(even) {
    padding-left: 20px;
  }
`;

export const Content = styled.div`
  flex-grow: 1;
`;

export const Text = styled.div`
  color: ${({theme}) => theme.color.grey100};
  margin-top: 10px;
`;
