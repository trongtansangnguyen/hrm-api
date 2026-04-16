import http from 'k6/http';
import { check } from 'k6';
import { Counter } from 'k6/metrics';

export const options = {
  scenarios: {
    attendance_500_requests: {
      executor: 'shared-iterations',
      vus: 50,
      iterations: 5000,
      maxDuration: '10m',
    },
  },
  thresholds: {
    attendance_checkin_connection_failures: ['count<1'],
  },
};

const baseUrl = 'http://127.0.0.1/hrm-api/public';
const token = '20|COXyIrP9KwnntWgVIzlWGVZVFFwLsa5jrwtQN9ZK101239f5';
const success200 = new Counter('attendance_checkin_200');
const duplicate422 = new Counter('attendance_checkin_422');
const unexpectedStatus = new Counter('attendance_checkin_unexpected_status');
const connectionFailures = new Counter('attendance_checkin_connection_failures');

export default function () {
  const payload = JSON.stringify({
    latitude: -6.2000000,
    longitude: 106.8166667,
  });

  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    Authorization: `Bearer ${token}`,
    'X-Device-Id': 'device-loadtest',
  };

  const res = http.post(`${baseUrl}/api/attendance/check-in`, payload, { headers });

  if (res.status === 200) {
    success200.add(1);
  } else if (res.status === 422) {
    duplicate422.add(1);
  } else if (res.status === 0) {
    connectionFailures.add(1);
    console.log(`connection_error code=${res.error_code ?? 0} error="${res.error ?? ''}"`);
  } else {
    unexpectedStatus.add(1);
    console.log(`unexpected_status status=${res.status} error_code=${res.error_code ?? 0} body=${res.body}`);
  }

  check(res, {
    'status is 200 or 422 without connection error': (r) =>
      r.status === 200 || r.status === 422,
  });
}
