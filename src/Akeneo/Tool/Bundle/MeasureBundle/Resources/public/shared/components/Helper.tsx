import styled from 'styled-components';

const Helper = styled.div`
  background-color: ${props => props.theme.color.blue10};
  display: flex;
  align-items: center;
  padding: 20px;
  min-height: 80px;
  margin-bottom: 20px;
  width: 100%;
  line-height: 25px;
`;

const HelperTitle = styled.div`
  color: ${props => props.theme.color.grey140};
  font-size: ${props => props.theme.fontSize.bigger};
  font-weight: 600;
  margin-left: 20px;
  border-left: 1px solid ${props => props.theme.color.grey80};
  padding-left: 20px;
`;

const HelperText = styled.div`
  color: ${props => props.theme.color.grey120};
  font-size: ${props => props.theme.fontSize.default};
  font-weight: 400;
  line-height: 1.5;
`;

export {Helper, HelperTitle, HelperText};
