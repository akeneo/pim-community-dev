import styled from 'styled-components';

const FieldColumn = styled.span`
  width: 120px;
  display: inline-block;
  padding: 0 2px;
  white-space: nowrap;
  overflow: hidden;
  height: 40px;
  line-height: 40px;
  margin: 0 20px 0 0;
`;

const OperatorColumn = styled.span`
  width: 160px;
  display: inline-block;
  margin: 0 20px 0 0;
  height: 40px;
`;

const ValueColumn = styled.span<{small?: boolean}>`
  &:not(:empty) {
    display: inline-flex;
    width: ${({small}) => (small ? '120px' : '300px')};
    margin: 0 20px 0 0;
    min-height: 40px;
    ${({small}) => (small ? 'display: flex;' : '')};
  }
`;

const LocaleColumn = styled.span`
  width: 120px;
  display: inline-block;
  margin: 0 20px 0 0;
  height: 40px;
`;

const ScopeColumn = styled.span`
  width: 120px;
  display: inline-block;
  margin: 0 20px 0 0;
  height: 40px;
`;

const ConditionErrorLine = styled.ul`
  &:not(:empty) {
    margin-left: 100px;
    margin-top: 15px;
    color: ${({theme}): string => theme.color.red100};
    background-color: ${({theme}): string => theme.color.red20};
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
      border-left: 1px solid ${({theme}): string => theme.color.red100};
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

const ConditionLineFormAndErrorsContainer = styled.div`
  display: flex;
  flex-direction: column;
`;

const ConditionLineFormContainer = styled.div`
  display: flex;
`;

const ConditionLineErrorsContainer = styled.div`
  &:not(:empty) {
    margin-top: 10px;
  }
`;

export {
  ConditionLineFormContainer,
  ConditionLineFormAndErrorsContainer,
  ConditionLineErrorsContainer,
  ConditionErrorLine,
  FieldColumn,
  LocaleColumn,
  OperatorColumn,
  ScopeColumn,
  ValueColumn,
};
