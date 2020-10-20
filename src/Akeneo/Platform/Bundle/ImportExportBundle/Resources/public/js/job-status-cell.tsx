import ReactDOM from "react-dom";
import React from "react";
import JobExecutionStatus = require('./job-status-badge');
import {ThemeProvider} from "styled-components";
import {pimTheme} from "akeneo-design-system";
import {DependenciesProvider} from "@akeneo-pim-community/legacy-bridge";
const StringCell = require('oro/datagrid/string-cell');

class JobStatusCell extends StringCell {
    render() {
        const status = this.model.get('status');
        const jobExecutionId = this.model.get('id');

        ReactDOM.render(
          <DependenciesProvider>
              <ThemeProvider theme={pimTheme}>
                  <JobExecutionStatus jobExecutionId={jobExecutionId} status={status}/>
              </ThemeProvider>
          </DependenciesProvider>,
            this.el
        );

        return this;
    }
}

export = JobStatusCell;
