import styled from 'styled-components';
import infoIcon from '../../../assets/icons/info.svg';

const dangerIcon = '/bundles/pimui/images/icon-danger.svg';

export const HelperContainer = styled.div`
  margin-top: 15px;
`;

export const InlineHelper = styled.div<{
  info?: true;
  warning?: true;
  danger?: true;
}>`
  background-size: 16px;
  background-image: url(${infoIcon});
  background-image: url(${props => {
    if (props.danger) {
      return dangerIcon;
    }
    if (props.warning) {
      return '';
    }
    return infoIcon;
  }});
  background-repeat: no-repeat;
  background-position: left top;
  color: ${props =>
    props.danger ? props.theme.color.red100 : props.theme.color.grey120};
  font-size: ${({theme}) => theme.fontSize.small};
  line-height: 15px;
  padding-left: 26px;
  a {
    color: ${props =>
      props.danger ? props.theme.color.red100 : props.theme.color.blue100};
    font-weight: 700;
    text-decoration: underline;
  }
`;
