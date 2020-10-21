import ReactDOM from "react-dom";
import React from "react";
import JobExecutionStatus = require('./JobStatusBadge');
import {ThemeProvider} from "styled-components";
import {pimTheme} from "akeneo-design-system";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
const StringCell = require('oro/datagrid/string-cell');

class JobStatusCell extends StringCell {
    render() {
        const status = this.model.get('status');
        const jobExecutionId = this.model.get('id');
        // TODO:
        // Should I get hasError and hasWarning here too and pass it as props ?
        // Or should I let the component simply fetch the data all the time (and not just pass the jobExecutionId without the status =>
        // which would make this component more generic in my opinion but less performance efficient)

        ReactDOM.render(
          <DependenciesProvider>
              <ThemeProvider theme={pimTheme}>
                  <JobExecutionStatus jobExecutionId={jobExecutionId} status={status} />
              </ThemeProvider>
          </DependenciesProvider>,
            this.el
        );

        return this;
    }
}

export = JobStatusCell;
