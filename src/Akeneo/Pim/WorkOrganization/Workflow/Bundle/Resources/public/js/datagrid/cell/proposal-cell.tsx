import ReactDOM from 'react-dom';
import React from 'react';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {Proposal} from './Proposal';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
const StringCell = require('oro/datagrid/string-cell');

class ProposalCell extends StringCell {
  constructor(options) {
    super({
      ...options,
      className: 'AknGrid-bodyCell AknGrid-bodyCell--full',
    });
  }

  render() {
    const formattedChanges: string = this.model.get('formatted_changes');
    const documentType = this.model.get('document_type');
    const documentId = this.model.get('document_id');
    const documentLabel = this.model.get('document_label');
    const authorLabel = this.model.get('author_label');
    const createdAt = this.model.get('createdAt');
    const proposalId = this.model.get('proposal_id');

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
          />
        </ThemeProvider>
      </DependenciesProvider>,
      this.el
    );
    return this;
  }
}

export = ProposalCell;
