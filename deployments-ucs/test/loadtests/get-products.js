import { sleep } from 'k6';
import http from 'k6/http';
import encoding from 'k6/encoding';

export const options = {
  duration: '1m',
  vus: 10,
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(90)<500'],
    http_req_waiting: ['p(90)<500'],
  },
};

const url = `https://${__ENV.FQDN}`;
const credentials = `${__ENV.API_CLIENT_ID}:${__ENV.API_SECRET}`;
const encodedCredentials = encoding.b64encode(credentials);

export function setup() {
  // Create authentication request and return access_token
  const headers = {
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Basic ${encodedCredentials}`,
    },
  };
  const data = {
    "username" : `${__ENV.API_USERNAME}`,
    "password" : `${__ENV.API_PASSWORD}`,
    "grant_type": "password"
  };

  const res = http.post(`${url}/api/oauth/v1/token`, JSON.stringify(data), headers);
  sleep(1);
  console.log(`${res.status} ${res.body}`);
  console.log(`${credentials} : ${encodedCredentials}`);

  const access_token = res.json().access_token;
  return access_token;
}

export default function (access_token) {
  // Call API produtcts
  const headers = {
    headers: {
      "Content-Type": "application/json",
      "Authorization": `Bearer ${access_token}`,
    },
  };

  const res = http.get(`${url}/api/rest/v1/products`, headers);
  sleep(1);
  console.log(`${res.status} ${res.body}`);
}
