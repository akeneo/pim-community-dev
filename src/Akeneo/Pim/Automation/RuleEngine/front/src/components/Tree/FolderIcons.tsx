import React from 'react';
import styled from 'styled-components';
import {NodeType, isBranch} from './tree.types';
import {Translate} from '../../dependenciesTools';

const FolderImg = styled.img`
  padding: 0 5px;
`;

type Props = {
  nodeType: NodeType;
  selected?: boolean;
  translate: Translate;
  onClick: () => void;
};

const FolderIcons: React.FC<Props> = ({
  nodeType,
  selected = false,
  translate,
  onClick,
}) => {
  const branch = isBranch(nodeType);
  if (branch && selected) {
    return (
      <FolderImg
        src='/bundles/pimui/images/jstree/icon-foldersfull.svg'
        alt={translate('pimee_catalog_rule.form.category.folders.selected')}
        onClick={onClick}
      />
    );
  }
  if (branch) {
    return (
      <FolderImg
        src='/bundles/pimui/images/jstree/icon-folders.svg'
        alt={translate('pimee_catalog_rule.form.category.folders.not_selected')}
        onClick={onClick}
      />
    );
  }
  if (!branch && selected) {
    return (
      <FolderImg
        src='/bundles/pimui/images/jstree/icon-folderfull.svg'
        alt={translate('pimee_catalog_rule.form.category.folder.selected')}
        onClick={onClick}
      />
    );
  }
  return (
    <FolderImg
      src='/bundles/pimui/images/jstree/icon-folder.svg'
      alt={translate('pimee_catalog_rule.form.category.folder.not_selected')}
      onClick={onClick}
    />
  );
};

export {FolderIcons};
