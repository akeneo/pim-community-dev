// import * as React from 'react';
// import Dropdown from 'pimfront/app/application/component/dropdown';
// import __ from 'pimfront/tools/translator';
// import {Identifier} from 'pimfront/product-grid/domain/model/filter/property/identifier';
// import {Filter} from 'pimfront/product-grid/domain/model/filter/filter';

// interface FilterViewState {
//   filter: Filter;
// }

// export default class GridView extends React.Component<FilterViewState, {isOpen: boolean; filter: Filter}> {
//   constructor(props: FilterViewState) {
//     super(props);

//     this.state = {
//       isOpen: false,
//       filter: props.filter,
//     };
//   }

//   componentWillReceiveProps(nextProps: FilterViewState) {
//     this.setState({filter: nextProps.filter});
//   }

//   render() {
//     const filter = this.props.filter;

//     return (
//       <div className="AknFilterBox-filterContainer" data-name="{this.props.filter.field}" data-type="string">
//         <div className="AknFilterBox-filter">
//           <span className="AknFilterBox-filterLabel">{filter.field.identifier}</span>
//           <span className="AknFilterBox-filterCriteria">{filter.operator.identifier}</span>
//           <span className="AknFilterBox-filterCaret" />
//         </div>
//         <div className="filter-criteria">
//           <div className="AknFilterChoice">
//             <div className="AknFilterChoice-header">
//               <div className="AknFilterChoice-title">{filter.field.identifier}</div>
//               <Dropdown
//                 elements={Identifier.getOperators()}
//                 label={__('pim.grid.choice_filter.operator')}
//                 selectedElement={filter.operator.identifier}
//                 onSelectionChange={() => {}}
//               />
//             </div>
//             <div>
//               <input type="text" name="value" className="AknTextField" />
//             </div>
//             <div className="AknFilterChoice-button">
//               <button type="button" className="AknButton AknButton--apply">
//                 {__('Update')}
//               </button>
//             </div>
//           </div>
//         </div>
//         <div className="AknFilterBox-disableFilter AknIconButton AknIconButton--small AknIconButton--remove" />
//       </div>
//     );
//   }
// }
