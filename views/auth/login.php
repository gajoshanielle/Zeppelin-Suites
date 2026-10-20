<?php
$activePage = 'login';
include __DIR__ . '/../components/public_navbar.php';
?>

<!-- Main Section: Two-Column Login View -->
<main class="flex flex-1 w-full min-h-[calc(100vh-80px)]">
    <!-- Left Hero Image Banner -->
    <div class="hidden md:block w-1/2 min-h-full bg-cover bg-center bg-no-repeat relative"
        style="background-image: url('<?= htmlspecialchars($baseUrl) ?>/images/condo_photos/zeppelin-suites-slider-exterior-2.jpg');">
        <!-- Gradient Dark Grey Backdrop Overlay: Darkest on the very bottom, fading upwards -->
        <div class="absolute inset-0 pointer-events-none z-0"
            style="background: linear-gradient(to top, #020617 0%, rgba(2, 6, 23, 0.95) 15%, rgba(15, 23, 42, 0.85) 30%, rgba(15, 23, 42, 0.3) 45%, transparent 60%);">
        </div>

        <!-- Welcome Text sitting on top of the gradient -->
        <div class="absolute inset-0 z-10 flex flex-col justify-end p-12 text-white">
            <span class="text-xs uppercase font-semibold tracking-widest mb-2 text-slate-300">Welcome Home</span>
            <h2 class="text-3xl font-bold tracking-tight mb-2">Refined Living in the Heart of the City</h2>
            <p class="text-sm text-slate-200 max-w-md">Access your resident portal, manage reservations, and stay
                updated with Zeppelin Suites services.</p>
        </div>
    </div>

    <!-- Right Login Form Section -->
    <div class="w-full md:w-1/2 flex flex-col items-center justify-center p-6 sm:p-12 bg-white">
        <form action="<?= htmlspecialchars($actionUrl) ?>" method="POST"
            class="w-full max-w-sm flex flex-col items-center justify-center">
            <h1 class="text-3xl sm:text-4xl text-slate-900 font-semibold tracking-tight">Sign in</h1>
            <p class="text-sm text-slate-500 mt-2 text-center">Welcome back! Please enter your details to continue</p>

            <!-- Email Input -->
            <div
                class="flex items-center mt-8 w-full bg-white border border-slate-300 h-12 rounded-full overflow-hidden px-5 gap-3 transition-all focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900/10">
                <svg width="16" height="11" viewBox="0 0 16 11" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="shrink-0 text-slate-400">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                        d="M0 .55.571 0H15.43l.57.55v9.9l-.571.55H.57L0 10.45zm1.143 1.138V9.9h13.714V1.69l-6.503 4.8h-.697zM13.749 1.1H2.25L8 5.356z"
                        fill="currentColor" />
                </svg>
                <input type="email" name="email" placeholder="email@gmail.com"
                    class="bg-transparent text-slate-800 placeholder-slate-400 outline-none text-sm w-full h-full"
                    required autofocus>
            </div>

            <!-- Password input with eye toggle -->
            <div
                class="flex items-center mt-4 w-full bg-white border border-slate-300 h-12 rounded-full overflow-hidden pl-5 pr-4 gap-3 transition-all focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900/10">
                <svg width="13" height="17" viewBox="0 0 13 17" fill="none" xmlns="http://www.w3.org/2000/svg"
                    class="shrink-0 text-slate-400">
                    <path
                        d="M13 8.5c0-.938-.729-1.7-1.625-1.7h-.812V4.25C10.563 1.907 8.74 0 6.5 0S2.438 1.907 2.438 4.25V6.8h-.813C.729 6.8 0 7.562 0 8.5v6.8c0 .938.729 1.7 1.625 1.7h9.75c.896 0 1.625-.762 1.625-1.7zM4.063 4.25c0-1.406 1.093-2.55 2.437-2.55s2.438 1.144 2.438 2.55V6.8H4.061z"
                        fill="currentColor" />
                </svg>
                <input id="passwordInput" type="password" name="password" placeholder="Password"
                    class="bg-transparent text-slate-800 placeholder-slate-400 outline-none text-sm w-full h-full"
                    required>
                <button type="button" onclick="togglePasswordVisibility()"
                    class="cursor-pointer text-slate-400 hover:text-slate-800 transition-colors shrink-0 p-1 flex items-center justify-center focus:outline-none"
                    aria-label="Toggle password visibility">
                    <!-- Eye Open Icon -->
                    <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <!-- Eye Slash (Closed) Icon -->
                    <svg id="eyeClosedIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>

            <!-- Options Row -->
            <div class="w-full flex items-center justify-between mt-6 text-slate-500">
                <div class="flex items-center gap-2">
                    <input class="h-4 w-4 rounded accent-slate-900 cursor-pointer" type="checkbox" id="rememberCheckbox"
                        name="remember">
                    <label class="text-sm cursor-pointer select-none" for="rememberCheckbox">Remember me</label>
                </div>
                <button type="button" onclick="openForgotPasswordModal()"
                    class="text-sm text-slate-700 hover:text-slate-900 underline transition-colors cursor-pointer bg-transparent border-0 p-0">Forgot password?</button>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="mt-8 w-full h-12 rounded-full text-white bg-slate-900 hover:bg-slate-800 transition-all font-medium text-sm shadow-sm hover:shadow active:scale-[0.99] cursor-pointer">
                Sign in to Account
            </button>
        </form>
    </div>
</main>

<!-- Forgot Password Multi-Step Modal -->
<div id="forgotPasswordModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4 transition-opacity">
    <div
        class="bg-white p-6 sm:p-8 rounded-2xl w-full max-w-md shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200 relative">
        <!-- Close Button -->
        <button onclick="closeForgotPasswordModal()"
            class="absolute top-5 right-5 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer p-1 rounded-full hover:bg-slate-100"
            aria-label="Close modal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-2 mb-6">
            <div id="stepDot1" class="w-8 h-2 rounded-full bg-slate-900 transition-all"></div>
            <div id="stepDot2" class="w-8 h-2 rounded-full bg-slate-200 transition-all"></div>
            <div id="stepDot3" class="w-8 h-2 rounded-full bg-slate-200 transition-all"></div>
        </div>

        <!-- Feedback Alert Box -->
        <div id="fpAlert" class="hidden mb-4 p-3 rounded-xl text-xs font-medium leading-relaxed"></div>

        <!-- STEP 1: Enter Email -->
        <div id="fpStep1" class="space-y-4">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-800 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Reset Password</h3>
                <p class="text-xs text-slate-500 mt-1">Enter your registered email address and we'll send you a 6-digit verification code.</p>
            </div>

            <form onsubmit="handleSendCode(event)" class="space-y-4 mt-4">
                <div class="flex items-center w-full bg-white border border-slate-300 h-12 rounded-full overflow-hidden px-5 gap-3 focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900/10">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                    <input type="email" id="fpEmail" placeholder="yourname@gmail.com"
                        class="bg-transparent text-slate-800 placeholder-slate-400 outline-none text-sm w-full h-full"
                        required>
                </div>

                <button type="submit" id="fpBtnSend"
                    class="w-full h-11 rounded-full text-white bg-slate-900 hover:bg-slate-800 transition-all font-medium text-sm shadow-sm flex items-center justify-center cursor-pointer">
                    <span>Send Verification Code</span>
                </button>
            </form>
        </div>

        <!-- STEP 2: Enter Verification Code -->
        <div id="fpStep2" class="space-y-4 hidden">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Enter Verification Code</h3>
                <p class="text-xs text-slate-500 mt-1">Please enter the 6-digit code sent to <strong id="fpTargetEmailDisplay" class="text-slate-800"></strong>.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Code expires in 10 minutes.</p>
            </div>

            <form onsubmit="handleVerifyCode(event)" class="space-y-4 mt-4">
                <div class="flex items-center justify-center">
                    <input type="text" id="fpCode" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="123456"
                        class="w-48 text-center tracking-[0.4em] font-mono text-2xl font-bold py-2.5 border border-slate-300 rounded-xl focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none"
                        required autofocus>
                </div>

                <button type="submit" id="fpBtnVerify"
                    class="w-full h-11 rounded-full text-white bg-slate-900 hover:bg-slate-800 transition-all font-medium text-sm shadow-sm flex items-center justify-center cursor-pointer">
                    <span>Verify Code</span>
                </button>

                <div class="flex items-center justify-between text-xs text-slate-500 pt-2">
                    <button type="button" onclick="goToStep(1)" class="hover:text-slate-900 underline cursor-pointer">Change email</button>
                    <button type="button" id="fpBtnResend" onclick="handleResendCode()" class="hover:text-slate-900 underline cursor-pointer">Resend code</button>
                </div>
            </form>
        </div>

        <!-- STEP 3: Set New Password -->
        <div id="fpStep3" class="space-y-4 hidden">
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-slate-900">Set New Password</h3>
                <p class="text-xs text-slate-500 mt-1">Create a new secure password with at least 6 characters.</p>
            </div>

            <form onsubmit="handleResetPassword(event)" class="space-y-4 mt-4">
                <div class="flex items-center w-full bg-white border border-slate-300 h-12 rounded-full overflow-hidden px-5 gap-3 focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900/10">
                    <input type="password" id="fpNewPass" placeholder="New Password (min 6 characters)"
                        class="bg-transparent text-slate-800 placeholder-slate-400 outline-none text-sm w-full h-full"
                        required minlength="6">
                </div>

                <div class="flex items-center w-full bg-white border border-slate-300 h-12 rounded-full overflow-hidden px-5 gap-3 focus-within:border-slate-900 focus-within:ring-2 focus-within:ring-slate-900/10">
                    <input type="password" id="fpConfirmPass" placeholder="Confirm New Password"
                        class="bg-transparent text-slate-800 placeholder-slate-400 outline-none text-sm w-full h-full"
                        required minlength="6">
                </div>

                <button type="submit" id="fpBtnReset"
                    class="w-full h-11 rounded-full text-white bg-slate-900 hover:bg-slate-800 transition-all font-medium text-sm shadow-sm flex items-center justify-center cursor-pointer">
                    <span>Save New Password</span>
                </button>
            </form>
        </div>

        <!-- Success Complete Screen -->
        <div id="fpSuccess" class="space-y-4 hidden text-center py-3">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-slate-900">Password Changed!</h3>
            <p id="fpSuccessMsg" class="text-xs text-slate-600">Your password has been updated successfully. You can now log in with your new credentials.</p>

            <button type="button" onclick="closeForgotPasswordModal()"
                class="mt-4 w-full h-11 rounded-full text-white bg-slate-900 hover:bg-slate-800 transition-all font-medium text-sm shadow-sm flex items-center justify-center cursor-pointer">
                Return to Sign In
            </button>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal"
    class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4 transition-opacity">
    <div
        class="bg-white p-6 sm:p-7 rounded-2xl w-full max-w-sm text-center shadow-xl border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="w-12 h-12 rounded-full bg-red-50 text-red-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-900">Authentication Failed</h2>
        <p id="errorMessage" class="text-sm text-slate-600 mt-2 leading-relaxed"></p>

        <button onclick="closeModal()"
            class="mt-6 w-full bg-slate-900 hover:bg-slate-800 transition-colors text-white py-2.5 rounded-full text-sm font-medium cursor-pointer shadow-sm">
            Try Again
        </button>
    </div>
</div>

<script>
    const BASE_URL = <?= json_encode($baseUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
    let fpCurrentEmail = '';
    let fpCurrentCode = '';

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('passwordInput');
        const eyeOpenIcon = document.getElementById('eyeOpenIcon');
        const eyeClosedIcon = document.getElementById('eyeClosedIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpenIcon.classList.add('hidden');
            eyeClosedIcon.classList.remove('hidden');
        } else {
            passwordInput.type = 'password';
            eyeOpenIcon.classList.remove('hidden');
            eyeClosedIcon.classList.add('hidden');
        }
    }

    function showError(message) {
        document.getElementById("errorMessage").innerText = message;
        document.getElementById("errorModal").classList.remove("hidden");
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById("errorModal").classList.add("hidden");
        document.body.style.overflow = 'auto';
    }

    // --- Forgot Password Flow Functions ---
    function openForgotPasswordModal() {
        document.getElementById('forgotPasswordModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        goToStep(1);
    }

    function closeForgotPasswordModal() {
        document.getElementById('forgotPasswordModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        hideFpAlert();
    }

    function showFpAlert(message, isError = true) {
        const alertBox = document.getElementById('fpAlert');
        alertBox.textContent = message;
        alertBox.className = isError
            ? 'mb-4 p-3 rounded-xl text-xs font-medium leading-relaxed bg-red-50 text-red-700 border border-red-200 block'
            : 'mb-4 p-3 rounded-xl text-xs font-medium leading-relaxed bg-emerald-50 text-emerald-700 border border-emerald-200 block';
    }

    function hideFpAlert() {
        const alertBox = document.getElementById('fpAlert');
        alertBox.classList.add('hidden');
        alertBox.textContent = '';
    }

    function goToStep(step) {
        hideFpAlert();
        ['fpStep1', 'fpStep2', 'fpStep3', 'fpSuccess'].forEach(id => {
            document.getElementById(id).classList.add('hidden');
        });

        const dot1 = document.getElementById('stepDot1');
        const dot2 = document.getElementById('stepDot2');
        const dot3 = document.getElementById('stepDot3');

        dot1.className = 'w-8 h-2 rounded-full ' + (step >= 1 ? 'bg-slate-900' : 'bg-slate-200') + ' transition-all';
        dot2.className = 'w-8 h-2 rounded-full ' + (step >= 2 ? 'bg-slate-900' : 'bg-slate-200') + ' transition-all';
        dot3.className = 'w-8 h-2 rounded-full ' + (step >= 3 ? 'bg-slate-900' : 'bg-slate-200') + ' transition-all';

        if (step === 1) {
            document.getElementById('fpStep1').classList.remove('hidden');
            setTimeout(() => document.getElementById('fpEmail').focus(), 100);
        } else if (step === 2) {
            document.getElementById('fpStep2').classList.remove('hidden');
            document.getElementById('fpTargetEmailDisplay').textContent = fpCurrentEmail;
            setTimeout(() => document.getElementById('fpCode').focus(), 100);
        } else if (step === 3) {
            document.getElementById('fpStep3').classList.remove('hidden');
            setTimeout(() => document.getElementById('fpNewPass').focus(), 100);
        } else if (step === 4) {
            document.getElementById('fpSuccess').classList.remove('hidden');
        }
    }

    async function handleSendCode(e) {
        e.preventDefault();
        hideFpAlert();
        const email = document.getElementById('fpEmail').value.trim();
        if (!email) return;

        const btn = document.getElementById('fpBtnSend');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>Sending code...</span>';

        try {
            const formData = new FormData();
            formData.append('email', email);

            const res = await fetch(`${BASE_URL}/api/auth/forgot-password/send-code`, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (data.success) {
                fpCurrentEmail = email;
                goToStep(2);
                showFpAlert(data.message, false);
            } else {
                showFpAlert(data.message || 'Unable to send verification code.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showFpAlert('Network error. Please try again.');
        }
    }

    async function handleResendCode() {
        if (!fpCurrentEmail) return;
        hideFpAlert();
        const resendBtn = document.getElementById('fpBtnResend');
        resendBtn.disabled = true;
        resendBtn.textContent = 'Sending...';

        try {
            const formData = new FormData();
            formData.append('email', fpCurrentEmail);

            const res = await fetch(`${BASE_URL}/api/auth/forgot-password/send-code`, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend code';

            if (data.success) {
                showFpAlert('A new verification code has been dispatched.', false);
            } else {
                showFpAlert(data.message || 'Unable to resend code.');
            }
        } catch (err) {
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend code';
            showFpAlert('Network error while resending code.');
        }
    }

    async function handleVerifyCode(e) {
        e.preventDefault();
        hideFpAlert();
        const code = document.getElementById('fpCode').value.trim();
        if (!code || code.length !== 6) {
            showFpAlert('Please enter the complete 6-digit code.');
            return;
        }

        const btn = document.getElementById('fpBtnVerify');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>Verifying...</span>';

        try {
            const formData = new FormData();
            formData.append('email', fpCurrentEmail);
            formData.append('code', code);

            const res = await fetch(`${BASE_URL}/api/auth/forgot-password/verify-code`, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (data.success) {
                fpCurrentCode = code;
                goToStep(3);
                showFpAlert('Code verified! Enter your new password below.', false);
            } else {
                showFpAlert(data.message || 'Invalid or expired verification code.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showFpAlert('Network error. Please try again.');
        }
    }

    async function handleResetPassword(e) {
        e.preventDefault();
        hideFpAlert();
        const newPass = document.getElementById('fpNewPass').value;
        const confirmPass = document.getElementById('fpConfirmPass').value;

        if (newPass.length < 6) {
            showFpAlert('Password must be at least 6 characters long.');
            return;
        }

        if (newPass !== confirmPass) {
            showFpAlert('New password and confirmation do not match.');
            return;
        }

        const btn = document.getElementById('fpBtnReset');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span>Saving...</span>';

        try {
            const formData = new FormData();
            formData.append('email', fpCurrentEmail);
            formData.append('code', fpCurrentCode);
            formData.append('new_password', newPass);
            formData.append('confirm_password', confirmPass);

            const res = await fetch(`${BASE_URL}/api/auth/forgot-password/reset`, {
                method: 'POST',
                body: formData,
            });
            const data = await res.json();

            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (data.success) {
                goToStep(4);
            } else {
                showFpAlert(data.message || 'Failed to update password.');
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showFpAlert('Network error. Please try again.');
        }
    }

    <?php if (!empty($errorMessage)): ?>
        showError(<?= json_encode($errorMessage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
</script>
