import styled from 'styled-components';

const PreviewGrid = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fill, 180px);
  gap: 16px;
`;

const PreviewCard = styled.div`
  display: flex;
  flex-direction: column;
  height: 100%;
  text-align: center;
  border: 1px solid rgba(0, 0, 0, 0.1);
  box-shadow: rgba(0, 0, 0, 0.1) 0 1px 3px 0;
  border-radius: 4px;
`;

const PreviewContainer = styled.div`
  height: 110px;
  color: #a1a9b7;
  overflow: hidden;
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
`;

const LabelContainer = styled.div`
  padding: 8px 0px;
  max-width: 100%;
  white-space: nowrap;
  word-break: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
`;

export {PreviewGrid, PreviewCard, PreviewContainer, LabelContainer};
