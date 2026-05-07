<?php
require_once __DIR__ . '/../../../includes/guards/staff_guard.php';
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">COMPANY POLICIES</h4>
        <p class="text-sm text-gray-500 font-medium">Privacy, agreements, rules, and regulations</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-shield-halved text-petron-red"></i>
        <span class="text-sm">Staff Compliance</span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-user-lock text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">1. Privacy & Data Protection</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>Keep all customer and operational data confidential and accessible only to authorized personnel.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Never share your account ID and password.</li>
                <li>Do not expose sales records, inventory data, or customer details outside official operations.</li>
                <li>Immediately report suspicious access or unusual transactions.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-file-signature text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">2. Agreements & Proper System Use</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>All staff users agree to use the system accurately and honestly in support of branch operations.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Input only true and verifiable transaction data.</li>
                <li>Do not bypass workflows or create unofficial records outside the system.</li>
                <li>Use your assigned role permissions only for legitimate work activities.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-scale-balanced text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">3. Rules, Regulations & Accountability</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>Branch operations must follow company rules and approved governance standards.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Follow SOPs for sales, inventory updates, and shift-end reporting.</li>
                <li>Unapproved data edits or unauthorized access attempts are strictly prohibited.</li>
                <li>Violations may result in disciplinary action and system access revocation.</li>
            </ul>
        </div>
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 text-xs text-blue-800 leading-6">
        For detailed policy interpretation, coordinate with branch management and refer to the latest Petron corporate policy documents.
    </div>
</div>
