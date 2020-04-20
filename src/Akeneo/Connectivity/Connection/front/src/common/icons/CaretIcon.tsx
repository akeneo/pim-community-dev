import styled from '../styled-with-theme';

const BaseCaretIcon = styled.span`
    border-left: 4px solid transparent;
    border-right: 4px solid transparent;
    content: '';
    display: inline-block;
    height: 0;
    margin-bottom: 2px;
    margin-left: 5px;
    width: 0;
`;
const CaretDownIcon = styled(BaseCaretIcon)`
    border-bottom: 4px solid #67768a;
    border-top: none;
`;
const CaretUpIcon = styled(BaseCaretIcon)`
    border-bottom: none;
    border-top: 4px solid #67768a;
`;

export {CaretDownIcon, CaretUpIcon};
