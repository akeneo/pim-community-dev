import React from 'react';
import {fireEvent, render, screen, waitFor} from '../../../tests/test-utils';
import {AddPropertyButton} from '../AddPropertyButton';
import userEvent from '@testing-library/user-event';
import {PROPERTY_NAMES, Structure} from '../../../models';

jest.mock('@akeneo-pim-community/shared', () => ({
  ...jest.requireActual('@akeneo-pim-community/shared'),
  useTranslate: () => (i18nKey: string) => {
    return i18nKey;
  },
  useRouter: () => {
    return {
      generate: (key: string, params: unknown) => ({key, params}),
    };
  },
  useNotify: () => {
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    return () => {};
  },
  useUserContext: () => {
    return {
      get: (k: string) => {
        switch (k) {
          case 'catalogLocale':
            return 'en_US';
          case 'uiLocale':
            return 'en_US';
          default:
            throw new Error(`Unknown key ${k}`);
        }
      },
    };
  },
}));

const properties = [
  {
    id: 'system',
    text: 'System',
    children: [
      {id: 'free_text', text: 'Free text'},
      {id: 'auto_number', text: 'Auto Number'},
      {id: 'family', text: 'Family'},
    ],
  },
  {
    id: 'marketing',
    text: 'Marketing',
    children: [
      {id: 'brand', text: 'Brand', type: 'pim_catalog_simpleselect'},
      {id: 'designer', text: 'Designer', type: 'akeneo_reference_entity'},
    ],
  },
  {
    id: 'erp',
    text: 'ERP',
    children: [{id: 'supplier', text: 'Supplier', type: 'pim_catalog_simpleselect'}],
  },
  {
    id: 'technical',
    text: 'Technical',
    children: [
      {id: 'maximum_print_size', text: 'Maximum print size', type: 'pim_catalog_simpleselect'},
      {id: 'sensor_type', text: 'Sensor type', type: 'pim_catalog_simpleselect'},
      {id: 'camera_type', text: 'Camera type', type: 'pim_catalog_simpleselect'},
      {id: 'headphone_connectivity', text: 'Headphone connectivity', type: 'pim_catalog_simpleselect'},
      {id: 'maximum_video_resolution', text: 'Maximum video resolution', type: 'pim_catalog_simpleselect'},
    ],
  },
  {
    id: 'product',
    text: 'Product',
    children: [
      {id: 'wash_temperature', text: 'Wash temperature'},
      {id: 'color', text: 'Color'},
      {id: 'size', text: 'Size'},
      {id: 'eu_shoes_size', text: 'EU Shoes Size'},
    ],
  },
];

describe('AddPropertyButton', () => {
  beforeEach(() => {
    const fetchImplementation = jest.fn().mockImplementation(
      (
        requestArgs: {
          key: string;
          params: {
            search: string;
          };
        },
        args: {method: string}
      ) => {
        if (requestArgs.key === 'akeneo_identifier_generator_get_properties') {
          return Promise.resolve({
            ok: true,
            json: () => Promise.resolve(requestArgs.params.search === 'toto' ? [] : properties),
            statusText: '',
            status: 200,
          } as Response);
        }

        throw new Error(`Unmocked url "${requestArgs.key}" [${args.method}]`);
      }
    );
    jest.spyOn(global, 'fetch').mockImplementation(fetchImplementation);
  });

  it('allows search', async () => {
    render(<AddPropertyButton onAddProperty={jest.fn()} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Free text')).toBeInTheDocument();
    });

    const searchField = screen.getByTitle('pim_common.search');
    expect(searchField).toBeInTheDocument();

    userEvent.type(searchField, 'toto');
    await waitFor(() => {
      const notFoundText = screen.getByText('pim_common.no_search_result');
      expect(notFoundText).toBeInTheDocument();
    });

    userEvent.clear(searchField);
    userEvent.type(searchField, 'free');
    await waitFor(() => {
      expect(screen.getByText('Free text')).toBeInTheDocument();
    });

    fireEvent.keyDown(searchField, {key: 'Escape', code: 'Escape'});
    await waitFor(() => {
      expect(screen.queryByText('Free text')).not.toBeInTheDocument();
    });
  });

  it('adds a property free text', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Free text')).toBeInTheDocument();
    });
    expect(screen.getByText('Free text')).toBeInTheDocument();
    expect(screen.getByText('Auto Number')).toBeInTheDocument();

    fireEvent.click(screen.getByText('Free text'));
    expect(onAddProperty).toBeCalledWith({
      type: PROPERTY_NAMES.FREE_TEXT,
      string: '',
    });
  });

  it('adds a property auto number', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Auto Number')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Auto Number'));
    expect(onAddProperty).toBeCalledWith({
      type: PROPERTY_NAMES.AUTO_NUMBER,
      digitsMin: 1,
      numberMin: 1,
    });
  });

  it('adds a property auto number', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Family')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Family'));
    expect(onAddProperty).toBeCalledWith({
      type: PROPERTY_NAMES.FAMILY,
      process: {
        type: null,
      },
    });
  });

  it('adds a select property', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Free text')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Brand'));
    await waitFor(() => {
      expect(onAddProperty).toBeCalledWith({
        type: PROPERTY_NAMES.SIMPLE_SELECT,
        attributeCode: 'brand',
        process: {
          type: null,
        },
      });
    });
  });

  it('adds a reference entity property', async () => {
    const onAddProperty = jest.fn();
    render(<AddPropertyButton onAddProperty={onAddProperty} structure={[]} />);
    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => {
      expect(screen.getByText('Free text')).toBeInTheDocument();
    });

    fireEvent.click(screen.getByText('Designer'));
    await waitFor(() => {
      expect(onAddProperty).toBeCalledWith({
        type: PROPERTY_NAMES.REF_ENTITY,
        attributeCode: 'designer',
        process: {
          type: null,
        },
      });
    });
  });

  it('should not be able to add an auto number twice', async () => {
    const structureWithAutoNumber: Structure = [
      {
        type: PROPERTY_NAMES.AUTO_NUMBER,
        digitsMin: 0,
        numberMin: 5,
      },
    ];
    render(<AddPropertyButton onAddProperty={jest.fn()} structure={structureWithAutoNumber} />);

    const button = screen.getByRole('button');
    expect(screen.getByText('pim_identifier_generator.structure.add_element')).toBeInTheDocument();
    expect(button).toBeInTheDocument();

    fireEvent.click(button);
    await waitFor(() => expect(screen.getByText('Free text')).toBeInTheDocument());
    expect(screen.queryByText('Auto Number')).not.toBeInTheDocument();
  });
});
