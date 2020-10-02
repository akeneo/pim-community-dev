import ReactDOM from "react-dom";
import React from "react";
const StringCell = require('oro/datagrid/string-cell');
import JobExecutionStatus = require('./job-status-badge');

class JobStatusCell extends StringCell {
    render() {
        const status = this.model.get('status');
        const jobExecutionId = this.model.get('id');
        ReactDOM.render(
          <JobExecutionStatus jobExecutionId={jobExecutionId} status={status} />,
            this.el
        );

        return this;
    }
}

export = JobStatusCell;
