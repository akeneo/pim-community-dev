import React from 'react';
import styled from 'styled-components';
import {useTranslate} from '../../../dependenciesTools/hooks';

const PreviewBlock = styled.div`
  background-color: black;
  color: #ccc;
  width: calc((100vw - 580px) / 2);
  font-family: ${props => props.theme.fontFamily.monospaced};
`;

const PreviewOption: React.FC<{
  togglePreview: () => void;
}> = ({togglePreview}) => {
  const translate = useTranslate();

  return (
    <div
      className='rdw-inline-wrapper rdw-editor-toolbar-blockType'
      onClick={togglePreview}>
      <div className='rdw-option-wrapper' aria-selected='false'>
        {translate('pimee_catalog_rule.form.edit.preview')}
      </div>
    </div>
  );
};

export {PreviewBlock, PreviewOption};
