<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 md:gap-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2ZM9 7C9 5.34315 10.3431 4 12 4C13.6569 4 15 5.34315 15 7C15 8.65685 13.6569 10 12 10C10.3431 10 9 8.65685 9 7ZM12 14C7.58172 14 4 16.6863 4 20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20C20 16.6863 16.4183 14 12 14ZM6.34077 19C6.95672 17.3426 9.18834 16 12 16C14.8117 16 17.0433 17.3426 17.6592 19H6.34077Z"/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Persons</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $personsCount ?? '—' }}</h4>
            </div>
            <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">Total</span>
        </div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.665 3.75621C11.8762 3.65064 12.1247 3.65064 12.3358 3.75621L18.7807 6.97856L12.3358 10.2009C12.1247 10.3065 11.8762 10.3065 11.665 10.2009L5.22014 6.97856L11.665 3.75621ZM4.29297 8.19203V16.0946C4.29297 16.3787 4.45347 16.6384 4.70757 16.7654L11.25 20.0366V11.6513C11.1631 11.6205 11.0777 11.5843 10.9942 11.5426L4.29297 8.19203ZM12.75 20.037L19.2933 16.7654C19.5474 16.6384 19.7079 16.3787 19.7079 16.0946V8.19202L13.0066 11.5426C12.9229 11.5844 12.8372 11.6208 12.75 11.6516V20.037ZM13.0066 2.41456C12.3732 2.09786 11.6277 2.09786 10.9942 2.41456L4.03676 5.89319C3.27449 6.27432 2.79297 7.05342 2.79297 7.90566V16.0946C2.79297 16.9469 3.27448 17.726 4.03676 18.1071L10.9942 21.5857L11.3296 20.9149L10.9942 21.5857C11.6277 21.9024 12.3732 21.9024 13.0066 21.5857L19.9641 18.1071C20.7264 17.726 21.2079 16.9469 21.2079 16.0946V7.90566C21.2079 7.05342 20.7264 6.27432 19.9641 5.89319L13.0066 2.41456Z" fill=""/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Governorates</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $governoratesCount ?? '—' }}</h4>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.4478 2.10562C12.165 2.35871 12 2.71765 12 3.09404V3.99999H4.125C2.94987 3.99999 2 4.94987 2 6.125V19.875C2 21.0501 2.94987 22 4.125 22H19.875C21.0501 22 22 21.0501 22 19.875V6.125C22 4.94987 21.0501 3.99999 19.875 3.99999H14.906L14.906 3.09404C14.906 2.71542 14.7381 2.35458 14.4522 2.10149C14.1692 1.8484 13.7812 1.69775 13.382 1.69098L13.061 1.68784C12.8454 1.68413 12.6303 1.72401 12.4478 1.85217V2.10562ZM12.4478 2.10562L12.4477 1.85218C12.4477 1.85213 12.4478 2.10561 12.4478 2.10562ZM13.061 3.18784L13.382 3.19098C13.4002 3.19128 13.4183 3.19222 13.4364 3.1938H11.5636C11.5817 3.19222 11.5998 3.19128 11.618 3.19098L11.939 3.18784C12.1246 3.18574 12.3104 3.19663 12.5 3.21528C12.6896 3.19663 12.8754 3.18574 13.061 3.18784ZM4.125 5.49999H11.25V6.24999C11.25 6.66421 11.5858 6.99999 12 6.99999C12.4142 6.99999 12.75 6.66421 12.75 6.24999V5.49999H19.875C20.2202 5.49999 20.5 5.77981 20.5 6.125V19.875C20.5 20.2202 20.2202 20.5 19.875 20.5H4.125C3.77981 20.5 3.5 20.2202 3.5 19.875V6.125C3.5 5.77981 3.77981 5.49999 4.125 5.49999Z" fill=""/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Payments</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $paymentsCount ?? '—' }}</h4>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4 3.25C3.58579 3.25 3.25 3.58579 3.25 4V20C3.25 20.4142 3.58579 20.75 4 20.75H20C20.4142 20.75 20.75 20.4142 20.75 20V4C20.75 3.58579 20.4142 3.25 20 3.25H4ZM2.75 4C2.75 3.0335 3.5335 2.25 4.5 2.25H19.5C20.4665 2.25 21.25 3.0335 21.25 4V20C21.25 20.9665 20.4665 21.75 19.5 21.75H4.5C3.5335 21.75 2.75 20.9665 2.75 20V4ZM6.5 6.75C6.08579 6.75 5.75 7.08579 5.75 7.5C5.75 7.91421 6.08579 8.25 6.5 8.25H9.5C9.91421 8.25 10.25 7.91421 10.25 7.5C10.25 7.08579 9.91421 6.75 9.5 6.75H6.5ZM5.75 12C5.75 11.5858 6.08579 11.25 6.5 11.25H9.5C9.91421 11.25 10.25 11.5858 10.25 12C10.25 12.4142 9.91421 12.75 9.5 12.75H6.5C6.08579 12.75 5.75 12.4142 5.75 12ZM6.5 15.75C6.08579 15.75 5.75 16.0858 5.75 16.5C5.75 16.9142 6.08579 17.25 6.5 17.25H9.5C9.91421 17.25 10.25 16.9142 10.25 16.5C10.25 16.0858 9.91421 15.75 9.5 15.75H6.5ZM12.75 7.5C12.75 7.08579 13.0858 6.75 13.5 6.75H17.5C17.9142 6.75 18.25 7.08579 18.25 7.5C18.25 7.91421 17.9142 8.25 17.5 8.25H13.5C13.0858 8.25 12.75 7.91421 12.75 7.5ZM13.5 11.25C13.0858 11.25 12.75 11.5858 12.75 12C12.75 12.4142 13.0858 12.75 13.5 12.75H17.5C17.9142 12.75 18.25 12.4142 18.25 12C18.25 11.5858 17.9142 11.25 17.5 11.25H13.5ZM12.75 16.5C12.75 16.0858 13.0858 15.75 13.5 15.75H17.5C17.9142 15.75 18.25 16.0858 18.25 16.5C18.25 16.9142 17.9142 17.25 17.5 17.25H13.5C13.0858 17.25 12.75 16.9142 12.75 16.5Z" fill=""/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Service Requests</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $serviceRequestsCount ?? '—' }}</h4>
            </div>
        </div>
    </div>
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6">
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-500/10">
            <svg class="fill-brand-500 dark:fill-brand-400" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M4 3.25C3.58579 3.25 3.25 3.58579 3.25 4V20C3.25 20.4142 3.58579 20.75 4 20.75H20C20.4142 20.75 20.75 20.4142 20.75 20V4C20.75 3.58579 20.4142 3.25 20 3.25H4ZM2.75 4C2.75 3.0335 3.5335 2.25 4.5 2.25H19.5C20.4665 2.25 21.25 3.0335 21.25 4V20C21.25 20.9665 20.4665 21.75 19.5 21.75H4.5C3.5335 21.75 2.75 20.9665 2.75 20V4ZM8.25 6.5C8.25 6.08579 8.58579 5.75 9 5.75H15C15.4142 5.75 15.75 6.08579 15.75 6.5C15.75 6.91421 15.4142 7.25 15 7.25H9C8.58579 7.25 8.25 6.91421 8.25 6.5ZM6.5 10.25C6.08579 10.25 5.75 10.5858 5.75 11C5.75 11.4142 6.08579 11.75 6.5 11.75H17.5C17.9142 11.75 18.25 11.4142 18.25 11C18.25 10.5858 17.9142 10.25 17.5 10.25H6.5ZM5.75 15.5C5.75 15.0858 6.08579 14.75 6.5 14.75H17.5C17.9142 14.75 18.25 15.0858 18.25 15.5C18.25 15.9142 17.9142 16.25 17.5 16.25H6.5C6.08579 16.25 5.75 15.9142 5.75 15.5Z" fill=""/>
            </svg>
        </div>
        <div class="mt-5 flex items-end justify-between">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Departments</span>
                <h4 class="mt-2 text-title-sm font-bold text-gray-800 dark:text-white/90">{{ $departmentsCount ?? '—' }}</h4>
            </div>
        </div>
    </div>
</div>
