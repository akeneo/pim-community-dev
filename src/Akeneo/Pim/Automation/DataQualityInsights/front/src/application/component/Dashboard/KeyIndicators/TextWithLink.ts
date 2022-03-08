import styled from 'styled-components';

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
