import styled from 'styled-components';

const FieldColumn = styled.span`
  width: 100px;
  display: inline-block;
  padding: 0 2px;
  overflow: hidden;
  height: 40px;
  line-height: 40px;
  margin: 0 20px 0 0;
`;

const OperatorColumn = styled.span`
  width: 160px;
  display: inline-block;
  margin: 0 20px 0 0;
`;

const ValueColumn = styled.span`
  &:not(:empty) {
    width: 300px;
    display: inline-block;
    margin: 0 20px 0 0;
  }
`;

const LocaleColumn = styled.span`
  width: 120px;
  display: inline-block;
  margin: 0 20px 0 0;
`;

const ScopeColumn = styled.span`
  width: 120px;
  display: inline-block;
  margin: 0 20px 0 0;
`;

const ConditionErrorLine = styled.ul`
  &:not(:empty) {
    margin-left: 100px;
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
  &:first-child {
    margin-top: 0;
    margin-left: 0;
  }
`;

export {
  FieldColumn,
  OperatorColumn,
  ValueColumn,
  LocaleColumn,
  ScopeColumn,
  ConditionErrorLine,
};
