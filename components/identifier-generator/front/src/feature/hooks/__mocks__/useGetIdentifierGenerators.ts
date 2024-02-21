import {IdentifierGenerator, PROPERTY_NAMES, TEXT_TRANSFORMATION} from '../../models';

const mockedList: IdentifierGenerator[] = [
  {
    code: 'test',
    conditions: [],
    structure: [{type: PROPERTY_NAMES.FREE_TEXT, string: 'AKN'}],
    labels: {ca_ES: 'azeaze', en_US: 'Sku generator'},
    target: 'sku',
    delimiter: null,
    text_transformation: TEXT_TRANSFORMATION.NO,
  },
];

type Response = {
  data?: IdentifierGenerator[];
  isLoading: boolean;
  error: Error | null;
  refetch: () => void;
};

const useGetIdentifierGenerators = (): Response => {
  return {
    data: mockedList,
    // eslint-disable-next-line @typescript-eslint/no-empty-function
    refetch: () => {},
    isLoading: false,
    error: null,
  };
};

export {useGetIdentifierGenerators};
