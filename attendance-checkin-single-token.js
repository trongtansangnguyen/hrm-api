import http from 'k6/http';
import { check } from 'k6';
import { SharedArray } from 'k6/data';

// 1. Bảo k6 đọc cái file json vừa tạo
const tokens = new SharedArray('danh_sach_token', function () {
    return JSON.parse(open('./tokens.json')); // Đảm bảo file này nằm cùng chỗ với script
});

export const options = {
    vus: 500, // Test 50 người cùng lúc
    iterations: 500, // Bắn tổng cộng 500 request
};

export default function () {
    // 2. Thuật toán chia Token: 
    // __VU là ID của người dùng ảo (từ 1 đến 50). Nó sẽ bốc đúng 1 token riêng biệt để xài.
// Nếu file tokens.json của ní có dạng ["token1", "token2", ...]
    const myToken = tokens[(__VU - 1) % tokens.length];
    const url = 'http://localhost/hrm-api/public/api/attendance/check-in';
    const payload = JSON.stringify({
        // Nếu có truyền data gì lên thì nhét vào đây
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${myToken}`, // 3. Gắn token vào thẻ VIP đi cửa trước
            // 'X-Forwarded-For': '127.0.0.1' // Mở comment dòng này nếu ní cần fake IP nội bộ
        },
    };

    // Bắn request
    const res = http.post(url, payload, params);

    // K6 in ra màn hình nếu bị lỗi (không phải 200 và cũng không phải 422)
    if (res.status !== 200 && res.status !== 422) {
        console.log(`LỖI RỒI NÍ ƠI! Status: ${res.status} - Nội dung: ${res.body}`);
    }

    // K6 tự động check xem có trả về 200 OK (Điểm danh thành công) không
    check(res, {
    'He thong phan hoi dung (200 hoặc 422)': (r) => r.status === 200 || r.status === 422,
    });
}