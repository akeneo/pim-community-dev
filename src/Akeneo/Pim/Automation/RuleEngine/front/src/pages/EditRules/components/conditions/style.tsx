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

export { FieldColumn, OperatorColumn, ValueColumn, LocaleColumn, ScopeColumn };
