import ReactDOM from 'react-dom';
import React from 'react';
import JobStatusBadge = require('./job-status-badge');
const StringCell = require('oro/datagrid/string-cell');

class JobStatusCell extends StringCell {
    render() {
        const status = this.model.get('status');
        const jobId = this.model.get('id');
        ReactDOM.render(
            <JobStatusBadge jobId={jobId} status={status} />,
            this.el
        );
        return this;
    }
}
export = JobStatusCell;
