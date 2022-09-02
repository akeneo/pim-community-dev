import { sleep } from 'k6';
import http from 'k6/http';

export const options = {
  duration: '1m',
  vus: 100,
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(90)<500'],
    http_req_waiting: ['p(90)<500'],
  },
};

export default function () {
  http.get(`https://${__ENV.FQDN}/user/login`);
  sleep(1);
}
