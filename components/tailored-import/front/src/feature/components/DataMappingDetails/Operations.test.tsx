import React from 'react';
import {screen} from '@testing-library/react';
import {renderWithProviders} from '@akeneo-pim-community/shared';
import {Operations} from "./Operations";
import {AttributeTarget, DataMapping} from "../../models";

test("it don't display preview if no sample data is provided", () => {
    const dataMapping = {
        uuid: "1ff0d8da-2438-4f2a-ae29-2ea513100924",
        target: {} as AttributeTarget,
        sources: ["8cb22256-3bc7-494e-9b24-33a1c52fe758"],
        operations: [],
        sample_data: []
    } as DataMapping;

    renderWithProviders(<Operations dataMapping={dataMapping} />)

    expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
    expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).not.toBeInTheDocument();
})

test("it display preview if sample data is provided", () => {
    const dataMapping = {
        uuid: "1ff0d8da-2438-4f2a-ae29-2ea513100924",
        target: {} as AttributeTarget,
        sources: ["8cb22256-3bc7-494e-9b24-33a1c52fe758"],
        operations: [],
        sample_data: ["product_1","product_2","product_3"]
    } as DataMapping;

    renderWithProviders(<Operations dataMapping={dataMapping} />)

    expect(screen.getByText('akeneo.tailored_import.data_mapping.operations.title')).toBeInTheDocument();
    expect(screen.queryByText('akeneo.tailored_import.data_mapping.preview.title')).toBeInTheDocument();
    expect(screen.queryByText('product_1')).toBeInTheDocument();
    expect(screen.queryByText('product_2')).toBeInTheDocument();
    expect(screen.queryByText('product_3')).toBeInTheDocument();
})

