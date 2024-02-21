export const getExpectedAnnouncements = () => {
  return [
    {
      id: 'update-title_announcement-20-04-2020',
      title: 'Title announcement',
      description: 'Description announcement',
      img: '/path/img/announcement.png',
      altImg: 'alt announcement img',
      link: 'http://external.com',
      tags: ['new', 'updates'],
      startDate: '20-04-2020',
    },
    {
      id: 'update-title_announcement_2-20-04-2020',
      title: 'Title announcement 2',
      description: 'Description announcement 2',
      link: null,
      img: null,
      altImg: null,
      tags: ['product_marketing'],
      startDate: '20-04-2020',
    },
  ];
};

export const getExpectedPimAnalyticsData = () => {
  return {pim_edition: 'Serenity', pim_version: '129923839'};
};
