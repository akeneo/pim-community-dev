import styled from 'styled-components';

export const Subsection = styled.div.attrs(() => ({className: 'AknSubsection'}))``;

export const SubsectionHeader = styled.header.attrs(() => ({className: 'AknSubsection-title'}))`
  line-height: 41px;
  height: 43px;
`;

export const SubsectionHeaderFilters = styled.div`
  display: flex;
  flex-wrap: wrap;
`;
