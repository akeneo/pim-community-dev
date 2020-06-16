export const getExpectedAnnouncements = () => {
  return [
    {
      title: 'Title announcement',
      description: 'Description announcement',
      img: '/path/img/announcement.png',
      altImg: 'alt announcement img',
      link: 'http://external.com',
      tags: ['new', 'updates'],
      startDate: '20-04-2020',
      notificationDuration: 7,
      editions: ['CE', 'EE'],
    },
    {
      title: 'Title announcement 2',
      description: 'Description announcement 2',
      link: 'http://external-2.com',
      img: null,
      altImg: null,
      tags: ['tag'],
      startDate: '20-04-2020',
      notificationDuration: 14,
      editions: ['CE'],
    },
  ];
};

export const getExpectedPimAnalyticsData = () => {
  return {pim_edition: 'Serenity', pim_version: '129923839'};
};
