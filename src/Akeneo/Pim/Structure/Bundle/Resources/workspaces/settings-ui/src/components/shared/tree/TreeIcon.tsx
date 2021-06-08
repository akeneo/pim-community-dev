import React from 'react';
import styled, {css} from 'styled-components';
import {FolderIcon, FolderPlainIcon, FoldersIcon, FoldersPlainIcon, getColor, LoaderIcon} from 'akeneo-design-system';

const folderIconCss = css`
  vertical-align: middle;
  transition: color 0.2s ease;
  margin-right: 5px;
  pointer-events: none;
`;

const TreeLeafNotSelectedIcon = styled(FolderIcon)`
  ${folderIconCss}
`;

const TreeFolderSelectedIcon = styled(FoldersPlainIcon)`
  color: ${getColor('blue100')};
  ${folderIconCss}
`;

const TreeLeafSelectedIcon = styled(FolderPlainIcon)`
  color: ${getColor('blue100')};
  ${folderIconCss}
`;

const TreeFolderNotSelectedIcon = styled(FoldersIcon)`
  ${folderIconCss}
`;

const TreeLoaderIcon = styled(LoaderIcon)`
  color: ${getColor('grey100')};
  ${folderIconCss}
`;

const TreeIcon: React.FC<{
  isLoading: boolean;
  isLeaf: boolean;
  selected: boolean;
}> = ({isLoading, isLeaf, selected}) => {
  if (isLoading) {
    return <TreeLoaderIcon size={24} />;
  }

  if (isLeaf) {
    return selected ? <TreeLeafSelectedIcon size={24} /> : <TreeLeafNotSelectedIcon size={24} />;
  }

  return selected ? <TreeFolderSelectedIcon size={24} /> : <TreeFolderNotSelectedIcon size={24} />;
};

export {TreeIcon};
