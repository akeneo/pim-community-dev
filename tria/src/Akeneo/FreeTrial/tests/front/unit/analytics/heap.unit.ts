import Heap from '../../../../back/Infrastructure/Symfony/Resources/public/js/analytics/heap';
import {getHeapAgent} from '../../../../back/Infrastructure/Symfony/Resources/public/js/analytics/heap-agent';

jest.mock('../../../../back/Infrastructure/Symfony/Resources/public/js/analytics/heap-agent');

describe('Heap init', () => {
  test('it identifies and add user properties at initialization', async () => {
    const mockedHeap = {
      identify: jest.fn(),
      addUserProperties: jest.fn(),
    };
    getHeapAgent.mockResolvedValue(mockedHeap);

    const UserContext = require('pim/user-context');
    UserContext.get.mockImplementation((data: string) => {
      switch (data) {
        case 'username':
          return 'juliaStark';
        case 'email':
          return 'julia@akeneo.com';
        case 'first_name':
          return 'Julia';
        case 'last_name':
          return 'Stark';
        default:
          return data;
      }
    });

    await Heap.init();

    expect(mockedHeap.identify).toHaveBeenCalledWith('juliaStark');
    expect(mockedHeap.addUserProperties).toHaveBeenCalledWith({
      email: 'julia@akeneo.com',
      firstName: 'Julia',
      lastName: 'Stark',
    });
  });
});
