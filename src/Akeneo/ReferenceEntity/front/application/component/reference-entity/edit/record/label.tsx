import * as React from 'react';
import {connect} from 'react-redux';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import __ from 'akeneoreferenceentity/tools/translator';

interface StateProps {
  grid: {
    totalCount: number;
  };
}

class RecordLabel extends React.Component<StateProps> {
  render() {
    const {grid} = this.props;

    return (
      <React.Fragment>
        {__('pim_reference_entity.reference_entity.tab.records')}
        <span className="AknColumn-span">({grid.totalCount})</span>
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      grid: {
        totalCount: state.grid.totalCount,
      },
    };
  },
  () => {
    return {};
  }
)(RecordLabel);
