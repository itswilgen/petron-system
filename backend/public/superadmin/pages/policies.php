<?php
require_once __DIR__ . '/../../../includes/guards/superadmin_guard.php';
?>

<header class="bg-white py-4 px-4 md:px-8 shadow-sm flex justify-between items-center">
    <div>
        <h4 class="text-xl font-extrabold text-petron-blue uppercase tracking-tight">CORPORATE POLICIES</h4>
        <p class="text-sm text-gray-500 font-medium">Privacy, agreements, rules, and regulations overview</p>
    </div>
    <div class="hidden md:flex items-center gap-3 text-gray-500 font-semibold bg-gray-50 px-4 py-2 rounded-full border border-gray-100 shadow-inner">
        <i class="fa-solid fa-scale-balanced text-petron-red"></i>
        <span class="text-sm">Governance Reference</span>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-user-lock text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">1. Privacy & Data Governance</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>Corporate data handling must comply with confidentiality standards, role-based access controls, and approved audit procedures.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Enforce minimum-access principles across all branch roles.</li>
                <li>Protect personal, financial, and operational datasets from unauthorized disclosure.</li>
                <li>Ensure incident response and breach reporting protocols are documented and followed.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-file-signature text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">2. Agreements & Authorization Controls</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>All system accounts operate under formal internal agreements tied to accountability, traceability, and acceptable use.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Account issuance must remain tied to verified personnel and branch assignments.</li>
                <li>Role permissions must align with approved responsibilities and escalation matrices.</li>
                <li>Authentication credentials must be rotated and protected under security policy.</li>
            </ul>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-shield-halved text-petron-blue"></i>
            <h5 class="font-bold text-gray-800">3. Rules, Regulations & Enforcement</h5>
        </div>
        <div class="p-6 text-sm text-gray-700 leading-7">
            <p>All branches must comply with company operating regulations and relevant legal obligations for reporting and recordkeeping.</p>
            <ul class="mt-3 list-disc pl-5 space-y-2">
                <li>Operational entries must remain complete, accurate, and audit-ready.</li>
                <li>Unauthorized policy overrides or untracked data modifications are prohibited.</li>
                <li>Non-compliance may trigger sanctions, access suspension, or formal investigation.</li>
            </ul>
        </div>
    </div>

    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 text-xs text-blue-800 leading-6">
        This page is a governance reference. Final legal authority remains with Petron corporate policy issuances and approved compliance documents.
    </div>
</div>
