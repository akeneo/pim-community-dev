import React from 'react';
import {TableAttribute} from "../models";
import {FilterSelectorList} from "./FilterSelectorList";
import {FilterValuesMapping} from "./FilterValues";

type ProductExportBuilderFilterProps = {
  attribute: TableAttribute;
  editable: boolean;
  label: string;
  removable: boolean;
  filterValuesMapping: FilterValuesMapping;
}

const ProductExportBuilderFilter: React.FC<ProductExportBuilderFilterProps> = ({
  attribute,
  editable,
  label,
  removable,
  filterValuesMapping,
}) => {
  const handleChange = () => {
    console.log('handle change !');
  };
  return <div className="AknFieldContainer">
    <div className="AknFieldContainer-header">
      <label className="AknFieldContainer-label control-label">
        {label}
      </label>
    </div>
    <div className="AknFieldContainer-inputContainer">
      <FilterSelectorList attribute={attribute} filterValuesMapping={filterValuesMapping} onChange={handleChange} inline={true}/>
    </div>
  </div>;

}

export {ProductExportBuilderFilter};
