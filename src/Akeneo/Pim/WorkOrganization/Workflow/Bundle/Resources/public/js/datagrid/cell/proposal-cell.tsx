import ReactDOM from 'react-dom';
import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Proposal} from './Proposal';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ProposalDiffsConfig} from './ProposalChange';
const StringCell = require('oro/datagrid/string-cell');

class ProposalCell extends StringCell {
  constructor(options: any) {
    super({
      ...options,
      className: 'AknGrid-bodyCell AknGrid-bodyCell--unclickable',
    });
  }

  render() {
    const formattedChanges = this.model.get('formatted_changes');
    const documentType = this.model.get('document_type');
    const documentId = this.model.get('document_id');
    const documentLabel = this.model.get('document_label');
    const authorLabel = this.model.get('author_label');
    const authorCode = this.model.get('author_code');
    const createdAt = this.model.get('createdAt');
    const proposalId = this.model.get('proposal_id');
    const proposalDiffs = __moduleConfig.proposal_diffs as ProposalDiffsConfig;

    ReactDOM.render(
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <Proposal
            documentType={documentType}
            documentId={documentId}
            documentLabel={documentLabel}
            authorLabel={authorLabel}
            formattedChanges={formattedChanges}
            createdAt={createdAt}
            proposalId={proposalId}
            authorCode={authorCode}
            proposalDiffs={proposalDiffs}
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }
}

export = ProposalCell;
