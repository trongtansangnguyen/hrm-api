<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Employee Counter</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative m-0 grid min-h-screen place-items-center overflow-x-hidden bg-[#f6efe4] p-6 font-['Plus_Jakarta_Sans','Segoe_UI',sans-serif] text-[#1f1b16]">
    @php($employeeCount = \App\Models\Employee::count())
    @php($employeeCountApiUrl = route('employees.count'))

    <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-24 -top-32 h-[500px] w-[900px] rounded-full bg-[radial-gradient(circle,_#ffe8cc_0%,_transparent_70%)]"></div>
        <div class="absolute -bottom-36 -right-20 h-[400px] w-[700px] rounded-full bg-[radial-gradient(circle,_#ffd4c7_0%,_transparent_72%)]"></div>
    </div>

    <main class="relative w-full max-w-[680px] overflow-hidden rounded-[24px] border border-[#f1ddcf] bg-gradient-to-[160deg] from-[#fffefb] to-[#fffdfa] px-[18px] py-6 shadow-[0_20px_45px_rgba(88,55,28,0.12)] sm:px-7 sm:py-8">
        <div class="pointer-events-none absolute -right-[70px] -top-[80px] h-[220px] w-[220px] rounded-full bg-[radial-gradient(circle,_#ffd8cc_0%,_transparent_68%)]"></div>

        <h1 class="m-0 text-[clamp(28px,5vw,38px)] tracking-[0.5px]">Employee Live Counter</h1>
        <p class="mb-[26px] mt-[10px] text-[15px] text-[#7d6d5f]">Số lượng nhân viên sẽ tự động cập nhật khi có bản ghi mới.</p>

        <section id="employeeCounter" class="flex items-baseline gap-3 rounded-[18px] border border-[#f3dfd6] bg-white p-4 transition-[transform,box-shadow] duration-200 sm:p-5" aria-live="polite" aria-atomic="true">
            <p id="employeeCountValue" class="m-0 min-w-[2.2ch] text-[clamp(52px,12vw,90px)] font-bold leading-none text-[#d14c2f] font-['Space_Grotesk','Plus_Jakarta_Sans',sans-serif] tracking-[-0.02em] transition-transform duration-200">{{ $employeeCount }}</p>
            <p class="m-0 text-base font-semibold text-[#5b4f44]">nhân viên trong hệ thống</p>
        </section>

        <p id="eventStatus" class="mt-[18px] rounded-xl border border-dashed border-[#f5b39f] bg-[#fff7f4] px-3 py-[10px] text-sm text-[#7d6d5f]">Đang lắng nghe sự kiện <strong class="text-[#d14c2f]">EmployeeCreated</strong> và <strong class="text-[#d14c2f]">EmployeeDeleted</strong>...</p>
    </main>

    <script>
        window.addEventListener('load', function () {
            const counterEl = document.getElementById('employeeCounter');
            const valueEl = document.getElementById('employeeCountValue');
            const statusEl = document.getElementById('eventStatus');

            let currentValue = Number(valueEl.textContent) || 0;
            let animationFrame;
            const valueUpClasses = ['-translate-y-1', 'scale-[1.04]'];
            const valueDownClasses = ['translate-y-1', 'scale-[0.96]'];
            const valuePulseClasses = ['drop-shadow-[0_8px_14px_rgba(209,76,47,0.22)]'];

            function clearMotionClasses() {
                valueEl.classList.remove(...valueUpClasses, ...valueDownClasses, ...valuePulseClasses);
            }

            function animateCount(toValue) {
                const fromValue = currentValue;
                const duration = 360;
                const startedAt = performance.now();
                const isUp = toValue > fromValue;
                const isDown = toValue < fromValue;

                cancelAnimationFrame(animationFrame);
                clearMotionClasses();
                valueEl.classList.add(...valuePulseClasses);

                if (isUp) {
                    valueEl.classList.add(...valueUpClasses);
                }

                if (isDown) {
                    valueEl.classList.add(...valueDownClasses);
                }

                function tick(now) {
                    const progress = Math.min((now - startedAt) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const next = Math.round(fromValue + (toValue - fromValue) * eased);

                    valueEl.textContent = next;

                    if (progress < 1) {
                        animationFrame = requestAnimationFrame(tick);
                    } else {
                        currentValue = toValue;
                        setTimeout(clearMotionClasses, 120);
                    }
                }

                animationFrame = requestAnimationFrame(tick);
            }

            async function fetchEmployeeCount() {
                const response = await fetch(@json($employeeCountApiUrl), {
                    headers: {
                        Accept: 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const payload = await response.json();
                const total = payload?.data?.count;

                if (!Number.isFinite(total)) {
                    throw new Error('Invalid total value');
                }

                return total;
            }

            async function syncEmployeeCountAndUpdateStatus(type, eventData) {
                try {
                    const latestCount = await fetchEmployeeCount();
                    animateCount(latestCount);
                } catch (error) {
                    statusEl.textContent = 'Nhận event nhưng không thể đồng bộ số lượng nhân viên từ API.';
                    return;
                }

                if (type === 'created') {
                    const newEmployeeId = eventData?.employee?.id;
                    statusEl.innerHTML = newEmployeeId
                        ? `Đã nhận sự kiện tạo mới nhân viên: ID <strong>#${newEmployeeId}</strong>`
                        : `Đã nhận sự kiện tạo mới nhân viên`;
                    return;
                }

                const deletedEmployeeId = eventData?.employeeId;
                statusEl.innerHTML = deletedEmployeeId
                    ? `Đã nhận sự kiện xóa nhân viên: ID <strong>#${deletedEmployeeId}</strong>`
                    : `Đã nhận sự kiện xóa nhân viên`;
            }

            async function onEmployeeCreated(eventData) {
                await syncEmployeeCountAndUpdateStatus('created', eventData);
            }

            async function onEmployeeDeleted(eventData) {
                await syncEmployeeCountAndUpdateStatus('deleted', eventData);
            }

            if (!window.Echo) {
                statusEl.textContent = 'Echo chưa được khởi tạo, vui lòng kiểm tra cấu hình broadcast.';
                return;
            }

            // Với broadcastAs("EmployeeCreated"/"EmployeeDeleted"), Echo thường cần prefix dấu chấm.
            window.Echo.channel('public-updates')
                .listen('.EmployeeCreated', onEmployeeCreated)
                .listen('EmployeeCreated', onEmployeeCreated)
                .listen('.EmployeeDeleted', onEmployeeDeleted)
                .listen('EmployeeDeleted', onEmployeeDeleted);
        });
    </script>
</body>
</html>
