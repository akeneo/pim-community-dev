import ReactDOM from "react-dom";
import React from "react";
const StringCell = require('oro/datagrid/string-cell');
class JobStatusCell extends StringCell {
    render() {
        ReactDOM.render(
            <div>Hello World</div>,
            this.el
        );
        return this;
    }
}
export = JobStatusCell;
