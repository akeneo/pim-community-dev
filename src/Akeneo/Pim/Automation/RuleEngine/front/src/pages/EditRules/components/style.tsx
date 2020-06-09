import styled from 'styled-components';

const ErrorLine = styled.ul`
  &:not(:empty) {
    margin-left: 10%;
    margin-top: 15px;
    color: ${({ theme }): string => theme.color.red100};
    background: ${({ theme }): string => theme.color.red20};
    min-height: 44px;
    padding: 10px;
    flex-basis: 100%;
    line-height: 24px;
    font-weight: bold;
    background-image: url('/bundles/pimui/images/icon-danger.svg');
    background-repeat: no-repeat;
    background-size: 25px;
    background-position: 8px 9px;
    padding-left: 60px;

    &:before {
      content: '';
      border-left: 1px solid ${({ theme }): string => theme.color.red100};
      position: absolute;
      height: 22px;
      margin-left: -16px;
    }
  }
`;

export { ErrorLine };
