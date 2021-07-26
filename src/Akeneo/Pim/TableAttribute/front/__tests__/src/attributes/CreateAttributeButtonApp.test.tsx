import React from 'react';
import {renderWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {fireEvent, screen} from '@testing-library/react';
import {CreateAttributeButtonApp} from "../../../src/attribute/CreateAttributeButtonApp";
import fetchMock from 'jest-fetch-mock';

fetchMock.mockResponse((request: Request) => {
  if (request.url.includes('pim_enrich_attribute_type_index')) {
    return Promise.resolve(
      JSON.stringify({
        pim_catalog_table: 'pim_catalog_table',
        pim_catalog_text: 'pim_catalog_text',
      })
    );
  }

  throw new Error(`The "${request.url}" url is not mocked.`);
});

describe('CreateAttributeButtonApp', () => {
  it('should render the component', () => {
    renderWithProviders(<CreateAttributeButtonApp
      buttonTitle={'create'}
      onClick={jest.fn()}
      iconsMap={{}}
    />);

    expect(screen.getByText('create')).toBeInTheDocument();
  });

  it ('should callback confirm with selection of sub template', async () => {
    const handleClick = jest.fn();
    renderWithProviders(<CreateAttributeButtonApp
      buttonTitle={'create'}
      onClick={handleClick}
      iconsMap={{}}
    />);

    fireEvent.click(screen.getByText('create'));
    expect(await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_table')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_table'));

    expect(await screen.findByText('pim_table_attribute.templates.choose_template')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.templates.nutrition'));

    expect(await screen.findByText('pim_common.create')).toBeInTheDocument();
    fireEvent.change(screen.getByLabelText('pim_common.label'), {target: {value: 'A new attribute'}});
    fireEvent.focus(screen.getByPlaceholderText('Please enter a value in the Select input TODO'));
    expect(await screen.findByText('nutrition-eu')).toBeInTheDocument();
    fireEvent.click(screen.getByText('nutrition-eu'));
    fireEvent.click(screen.getByText('pim_common.confirm'));

    expect(handleClick).toBeCalledWith({
      "attribute_type": "pim_catalog_table",
      "code": "A_new_attribute",
      "label": "A new attribute",
      "template": "nutrition-eu",
    });
  });

  it ('should not display sub templates when there is no choice', async () => {
    renderWithProviders(<CreateAttributeButtonApp
      buttonTitle={'create'}
      onClick={jest.fn()}
      iconsMap={{}}
    />);

    fireEvent.click(screen.getByText('create'));
    expect(await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_table')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_table'));

    expect(await screen.findByText('pim_table_attribute.templates.choose_template')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_table_attribute.templates.empty_table'));

    expect(await screen.findByText('pim_common.create')).toBeInTheDocument();
    fireEvent.change(screen.getByLabelText('pim_common.label'), {target: {value: 'A new attribute'}});
    expect(screen.queryByPlaceholderText('Please enter a value in the Select input TODO')).not.toBeInTheDocument();
  });

  it ('should not display template selection', async () => {
    renderWithProviders(<CreateAttributeButtonApp
      buttonTitle={'create'}
      onClick={jest.fn()}
      iconsMap={{}}
    />);

    fireEvent.click(screen.getByText('create'));
    expect(await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text')).toBeInTheDocument();
    fireEvent.click(screen.getByText('pim_enrich.entity.attribute.property.type.pim_catalog_text'));

    expect(screen.queryByText('pim_table_attribute.templates.choose_template')).not.toBeInTheDocument();
  });

  it ('should close the modal', async () => {
    renderWithProviders(<CreateAttributeButtonApp
      buttonTitle={'create'}
      onClick={jest.fn()}
      iconsMap={{}}
    />);

    fireEvent.click(screen.getByText('create'));
    expect(await screen.findByText('pim_enrich.entity.attribute.property.type.pim_catalog_text')).toBeInTheDocument();
    fireEvent.click(screen.getByTitle('pim_common.close'));
    expect(screen.queryByText('pim_enrich.entity.attribute.property.type.pim_catalog_text')).not.toBeInTheDocument();
  });
});
