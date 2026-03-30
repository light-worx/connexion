<!DOCTYPE html>
<html class="h-full bg-gray-50">
<head>
    <title>Setup Your Site</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-2 bg-teal-600 rounded-lg shadow-md shadow-teal-100">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Database & Admin</h2>
        </div>
        
        <form id="setupForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">DB Host</label>
                <input type="text" name="db_host" value="127.0.0.1" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Database Name</label>
                <input type="text" name="db_name" placeholder="my_database" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">DB User</label>
                    <input type="text" name="db_user" placeholder="root" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">DB Password</label>
                    <input type="password" name="db_pass" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
                </div>
            </div>

            <div class="relative py-4">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-100"></div>
                </div>
                <div class="relative flex justify-center text-xs uppercase tracking-widest">
                    <span class="bg-white px-2 text-gray-400">Admin Account</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                <input type="email" name="admin_email" placeholder="admin@example.com" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Admin Password</label>
                <input type="password" name="admin_pass" class="block w-full border border-gray-200 rounded-xl p-3 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent outline-none transition-all">
            </div>

            <div class="flex gap-4 pt-4">
                <button type="button" id="testBtn" class="flex-1 bg-gray-100 text-gray-600 font-bold py-3 rounded-xl hover:bg-gray-200 transition-all">
                    Test
                </button>
                <button type="submit" id="submitBtn" class="flex-[2] bg-teal-600 text-white font-bold py-3 rounded-xl hover:bg-teal-700 transition-all shadow-lg shadow-teal-100 opacity-50 cursor-not-allowed" disabled>
                    Finalize Setup
                </button>
            </div>
            <div id="testMsg" class="mt-2 text-[11px] text-center font-medium"></div>
        </form>
        <div id="msg" class="mt-4 text-sm text-center font-medium"></div>
    </div>

    <script>
        document.getElementById('setupForm').onsubmit = async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const msg = document.getElementById('msg');
            
            btn.disabled = true;
            btn.innerText = 'Installing...';
            msg.className = "mt-4 text-teal-600 font-semibold";
            msg.innerText = 'Running migrations and creating account...';

            const formData = new FormData(e.target);
            const response = await fetch('/setup', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });

            const result = await response.json();
            if (response.ok) {
                msg.className = "mt-4 text-green-600 font-bold";
                msg.innerText = "✓ Success! Redirecting to login...";
                setTimeout(() => window.location.href = '/admin/login', 2000);
            } else {
                btn.disabled = false;
                btn.innerText = 'Try Again';
                msg.className = "mt-4 text-red-500";
                msg.innerText = result.error || "Something went wrong.";
            }
        };

        document.getElementById('testBtn').onclick = async () => {
            const btn = document.getElementById('testBtn');
            const submitBtn = document.getElementById('submitBtn');
            const msg = document.getElementById('testMsg');
            const form = document.getElementById('setupForm');
            const formData = new FormData(form);

            btn.innerText = '...';
            msg.innerText = '';

            try {
                const response = await fetch('/setup/test-db', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });

                const result = await response.json();

                if (response.ok) {
                    msg.className = "mt-2 text-[11px] text-center text-teal-600 font-bold";
                    msg.innerText = "✓ Connection Successful";
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                } else {
                    msg.className = "mt-2 text-[11px] text-center text-red-500 font-bold";
                    msg.innerText = "✕ Connection Failed";
                }
            } catch (e) {
                msg.innerText = "Error testing connection.";
            } finally {
                btn.innerText = 'Test';
            }
        };
    </script>
</body>
</html>