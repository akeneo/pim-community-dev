import {renderWithProviders} from "@akeneo-pim-community/shared";
import {screen} from "@testing-library/react";
import React from "react";
import {ProductFiles} from "./ProductFiles";

test('it renders an empty list', () => {
  renderWithProviders(<ProductFiles supplierIdentifier={'d8d5824b-afdb-41a9-93a4-6a76a8b15c08'}  />);
  expect(
    screen.getByText('supplier_portal.product_file_dropping.supplier_files.no_files')
  ).toBeInTheDocument();
});
