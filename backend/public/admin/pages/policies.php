<?php
require_once __DIR__ . '/../../../includes/guards/admin_guard.php';
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">COMPANY POLICIES</h4>
        <p class="text-sm text-gray-500 font-medium">Privacy, agreements, rules, and regulations</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-shield-halved text-petron-red"></i>
        <span class="text-sm">Internal Governance</span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-user-lock text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">1. Privacy & Data Protection</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>All employee, customer, and branch data must be kept confidential and used only for authorized operational purposes.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Do not share account credentials or system records with unauthorized persons.</li>
                <li>Access only the records needed for your assigned role and branch responsibility.</li>
                <li>Report any suspected data breach or unauthorized access immediately to management.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-file-signature text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">2. Agreements & Account Use</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>All users agree to use this system responsibly and to maintain accurate operational records.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Each user account is personal and must not be borrowed or transferred.</li>
                <li>All inventory, sales, and delivery entries must reflect real and verifiable transactions.</li>
                <li>Intentional falsification, deletion, or manipulation of records is prohibited.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-scale-balanced text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">3. Rules, Regulations & Compliance</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>System usage must follow company standards, branch operational procedures, and applicable legal requirements.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Follow established approval flow for pricing, delivery, and account-management actions.</li>
                <li>Maintain complete audit trails by using system functions instead of off-record changes.</li>
                <li>Policy violations may lead to access suspension and administrative action.</li>
            </ul>
        </div>
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 text-xs text-blue-800 leading-6">
        This page is for internal guidance. For official legal interpretation, refer to the latest corporate policy manual issued by Petron management.
    </div>
</div>
