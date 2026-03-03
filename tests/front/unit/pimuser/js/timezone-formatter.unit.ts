import {formatTimezoneOffsetFromUTC} from 'pimuser/js/tools/timezone-formatter';

test('it returns the offset of the timezone', () => {
  jest.useFakeTimers('modern');
  jest.setSystemTime(Date.UTC(2021, 10, 9, 12, 0, 0));

  expect(formatTimezoneOffsetFromUTC('Europe/Paris')).toEqual('+01:00');
  expect(formatTimezoneOffsetFromUTC('Australia/Melbourne')).toEqual('+11:00');
  expect(formatTimezoneOffsetFromUTC('Pacific/Tahiti')).toEqual('-10:00');
  expect(formatTimezoneOffsetFromUTC('America/New_York')).toEqual('-05:00');
  expect(formatTimezoneOffsetFromUTC('America/Martinique')).toEqual('-04:00');
  expect(formatTimezoneOffsetFromUTC('Asia/Kabul')).toEqual('+04:30');
  expect(formatTimezoneOffsetFromUTC('Asia/Kathmandu')).toEqual('+05:45');
  expect(formatTimezoneOffsetFromUTC('America/St_Johns')).toEqual('-03:30');
});

test('it take into account Daylight Saving Time of the timezone', () => {
  jest.useFakeTimers('modern');
  jest.setSystemTime(Date.UTC(2021, 9, 31, 0, 0, 0));
  expect(formatTimezoneOffsetFromUTC('Europe/Paris')).toEqual('+02:00');

  jest.setSystemTime(Date.UTC(2021, 9, 31, 1, 0, 0));
  expect(formatTimezoneOffsetFromUTC('Europe/Paris')).toEqual('+01:00');

  jest.setSystemTime(Date.UTC(2021, 10, 7, 8, 0, 0));
  expect(formatTimezoneOffsetFromUTC('America/Los_Angeles')).toEqual('-07:00');

  jest.setSystemTime(Date.UTC(2021, 10, 7, 9, 0, 0));
  expect(formatTimezoneOffsetFromUTC('America/Los_Angeles')).toEqual('-08:00');
});
