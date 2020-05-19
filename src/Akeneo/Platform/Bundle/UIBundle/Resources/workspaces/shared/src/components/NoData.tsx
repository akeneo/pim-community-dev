import styled from 'styled-components';

const NoDataSection = styled.div`
  text-align: center;
  margin-top: 70px;
`;

const NoDataTitle = styled.div`
  color: ${props => props.theme.color.grey140};
  font-size: ${props => props.theme.fontSize.title};
  text-align: center;
  margin: 30px 0 20px 0;
`;

const NoDataText = styled.div`
  color: ${props => props.theme.color.grey120};
  font-size: ${props => props.theme.fontSize.bigger};
  text-align: center;
`;

export {NoDataSection, NoDataTitle, NoDataText};
