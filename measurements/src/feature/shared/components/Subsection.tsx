import styled, {css} from 'styled-components';

const Subsection = styled.div.attrs(() => ({className: 'AknSubsection'}))``;

const SubsectionHeader = styled.header<{top?: number}>`
  border-bottom: 1px solid ${props => props.theme.color.grey140};
  color: ${props => props.theme.color.grey140};
  display: flex;
  height: 44px;
  justify-content: space-between;
  line-height: 44px;
  text-transform: uppercase;
  white-space: nowrap;

  ${props =>
    props.top !== undefined &&
    css`
      background: ${props => props.theme.color.white};
      position: sticky;
      top: ${(props: {top?: number}) => props.top}px;
      z-index: 2;
    `}
`;

export {Subsection, SubsectionHeader};
