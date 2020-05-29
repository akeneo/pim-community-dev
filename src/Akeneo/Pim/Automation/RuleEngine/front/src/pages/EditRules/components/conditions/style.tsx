import styled from 'styled-components';

const FieldColumn = styled.span`
  width: 10%;
  display: inline-block;
  padding: 0 2px;
  overflow: hidden;
  height: 40px;
  line-height: 40px;
`;

const OperatorColumn = styled.span`
  width: 10%;
  display: inline-block;
  padding: 0 2px;
`;

const ValueColumn = styled.span`
  width: 30%;
  display: inline-block;
  padding: 0 2px;
`;

const LocaleColumn = styled.span`
  width: 12%;
  display: inline-block;
  padding: 0 2px;
`;

const ScopeColumn = styled.span`
  width: 12%;
  display: inline-block;
  padding: 0 2px;
`;

const ConditionErrorLine = styled.ul`
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
    background-image: url("/bundles/pimui/images/icon-danger.svg");
    background-repeat: no-repeat;
    background-size: 25px;
    background-position: 8px 9px;
    padding-left: 60px;
    
    &:before {
      content: "";
      border-left: 1px solid ${({ theme }): string => theme.color.red100};
      position: absolute;
      height: 22px;
      margin-left: -16px;
    }
  }
`

export { FieldColumn, OperatorColumn, ValueColumn, LocaleColumn, ScopeColumn, ConditionErrorLine };
