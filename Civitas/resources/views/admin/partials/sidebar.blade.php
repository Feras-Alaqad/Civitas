<aside
    x-data="{}"
    :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
    class="fixed left-0 top-0 z-9999 flex h-screen w-[290px] flex-col border-r border-gray-200 bg-white dark:border-gray-800 dark:bg-black transition-transform duration-300 ease-in-out lg:transition-transform lg:duration-300"
>
    <div class="flex items-center justify-between px-5 pt-8 pb-7">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center overflow-hidden">
            <span class="logo shrink-0">
                <img class="dark:hidden" src="{{ asset('images/logo/logo.svg') }}" alt="Logo" />
                <img class="hidden dark:block" src="{{ asset('images/logo/logo-dark.svg') }}" alt="Logo" />
            </span>
        </a>
    </div>

    <div class="flex flex-col overflow-y-auto no-scrollbar flex-1">
        <nav class="flex-1">
            <div>
                <h3 class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400 px-5">Menu</h3>
                <ul class="flex flex-col gap-0.5 mb-6 px-3">
                    <!-- Dashboard -->
                    <li>
                        <a
                            href="{{ route('admin.dashboard') }}"
                            :class="page === 'dashboard'
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 group"
                        >
                            <svg :class="page === 'dashboard' ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" class="shrink-0 transition-colors duration-200" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M5.5 3.25C4.25736 3.25 3.25 4.25736 3.25 5.5V8.99998C3.25 10.2426 4.25736 11.25 5.5 11.25H9C10.2426 11.25 11.25 10.2426 11.25 8.99998V5.5C11.25 4.25736 10.2426 3.25 9 3.25H5.5ZM4.75 5.5C4.75 5.08579 5.08579 4.75 5.5 4.75H9C9.41421 4.75 9.75 5.08579 9.75 5.5V8.99998C9.75 9.41419 9.41421 9.74998 9 9.74998H5.5C5.08579 9.74998 4.75 9.41419 4.75 8.99998V5.5ZM5.5 12.75C4.25736 12.75 3.25 13.7574 3.25 15V18.5C3.25 19.7426 4.25736 20.75 5.5 20.75H9C10.2426 20.75 11.25 19.7427 11.25 18.5V15C11.25 13.7574 10.2426 12.75 9 12.75H5.5ZM4.75 15C4.75 14.5858 5.08579 14.25 5.5 14.25H9C9.41421 14.25 9.75 14.5858 9.75 15V18.5C9.75 18.9142 9.41421 19.25 9 19.25H5.5C5.08579 19.25 4.75 18.9142 4.75 18.5V15ZM12.75 5.5C12.75 4.25736 13.7574 3.25 15 3.25H18.5C19.7426 3.25 20.75 4.25736 20.75 5.5V8.99998C20.75 10.2426 19.7426 11.25 18.5 11.25H15C13.7574 11.25 12.75 10.2426 12.75 8.99998V5.5ZM15 4.75C14.5858 4.75 14.25 5.08579 14.25 5.5V8.99998C14.25 9.41419 14.5858 9.74998 15 9.74998H18.5C18.9142 9.74998 19.25 9.41419 19.25 8.99998V5.5C19.25 5.08579 18.9142 4.75 18.5 4.75H15ZM15 12.75C13.7574 12.75 12.75 13.7574 12.75 15V18.5C12.75 19.7426 13.7574 20.75 15 20.75H18.5C19.7426 20.75 20.75 19.7427 20.75 18.5V15C20.75 13.7574 19.7426 12.75 18.5 12.75H15ZM14.25 15C14.25 14.5858 14.5858 14.25 15 14.25H18.5C18.9142 14.25 19.25 14.5858 19.25 15V18.5C19.25 18.9142 18.9142 19.25 18.5 19.25H15C14.5858 19.25 14.25 18.9142 14.25 18.5V15Z" fill="currentColor"/>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Persons -->
                    <li>
                        <a
                            href="{{ route('admin.citizens') }}"
                            :class="page === 'citizens' || page === 'service'
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 group"
                        >
                            <svg :class="page === 'citizens' || page === 'service' ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" class="shrink-0 transition-colors duration-200" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2ZM9 7C9 5.34315 10.3431 4 12 4C13.6569 4 15 5.34315 15 7C15 8.65685 13.6569 10 12 10C10.3431 10 9 8.65685 9 7ZM12 14C7.58172 14 4 16.6863 4 20C4 20.5523 4.44772 21 5 21H19C19.5523 21 20 20.5523 20 20C20 16.6863 16.4183 14 12 14ZM6.34077 19C6.95672 17.3426 9.18834 16 12 16C14.8117 16 17.0433 17.3426 17.6592 19H6.34077Z" fill="currentColor"/>
                            </svg>
                            <span>Persons</span>
                        </a>
                    </li>

                    <!-- Audit Logs -->
                    <li>
                        <a
                            href="{{ route('admin.audit-logs') }}"
                            :class="page === 'audit-logs'
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 group"
                        >
                            <svg :class="page === 'audit-logs' ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" class="shrink-0 transition-colors duration-200" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM3.5 12C3.5 7.30558 7.30558 3.5 12 3.5C16.6944 3.5 20.5 7.30558 20.5 12C20.5 16.6944 16.6944 20.5 12 20.5C7.30558 20.5 3.5 16.6944 3.5 12ZM12 7C10.8954 7 10 7.89543 10 9V11H8V13H10V15C10 16.1046 10.8954 17 12 17C13.1046 17 14 16.1046 14 15V13H16V11H14V9C14 7.89543 13.1046 7 12 7ZM12 9.5C12.2761 9.5 12.5 9.72386 12.5 10V12H11.5V10C11.5 9.72386 11.7239 9.5 12 9.5ZM12 14.5C12.2761 14.5 12.5 14.7239 12.5 15C12.5 15.2761 12.2761 15.5 12 15.5C11.7239 15.5 11.5 15.2761 11.5 15C11.5 14.7239 11.7239 14.5 12 14.5Z" fill="currentColor"/>
                            </svg>
                            <span>Audit Logs</span>
                        </a>
                    </li>

                    <!-- Payments -->
                    <li>
                        <a
                            href="{{ route('admin.payments') }}"
                            :class="page === 'payments'
                                ? 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400 shadow-sm'
                                : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/[0.05]'"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 group"
                        >
                            <svg :class="page === 'payments' ? 'text-brand-500 dark:text-brand-400' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300'" class="shrink-0 transition-colors duration-200" width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12.4478 2.10562C12.165 2.35871 12 2.71765 12 3.09404V3.99999H4.125C2.94987 3.99999 2 4.94987 2 6.125V19.875C2 21.0501 2.94987 22 4.125 22H19.875C21.0501 22 22 21.0501 22 19.875V6.125C22 4.94987 21.0501 3.99999 19.875 3.99999H14.906L14.906 3.09404C14.906 2.71542 14.7381 2.35458 14.4522 2.10149C14.1692 1.8484 13.7812 1.69775 13.382 1.69098L13.061 1.68784C12.8454 1.68413 12.6303 1.72401 12.4478 1.85217V2.10562ZM13.0066 2.41456C12.3732 2.09786 11.6277 2.09786 10.9942 2.41456L4.03676 5.89319C3.27449 6.27432 2.79297 7.05342 2.79297 7.90566V16.0946C2.79297 16.9469 3.27448 17.726 4.03676 18.1071L10.9942 21.5857C11.6277 21.9024 12.3732 21.9024 13.0066 21.5857L19.9641 18.1071C20.7264 17.726 21.2079 16.9469 21.2079 16.0946V7.90566C21.2079 7.05342 20.7264 6.27432 19.9641 5.89319L13.0066 2.41456ZM4.5 9C4.5 8.58579 4.83579 8.25 5.25 8.25H9.75C10.1642 8.25 10.5 8.58579 10.5 9V13.5C10.5 13.9142 10.1642 14.25 9.75 14.25H5.25C4.83579 14.25 4.5 13.9142 4.5 13.5V9ZM6 9.75V12.75H9V9.75H6ZM13.5 9C13.5 8.58579 13.8358 8.25 14.25 8.25H18.75C19.1642 8.25 19.5 8.58579 19.5 9C19.5 9.41421 19.1642 9.75 18.75 9.75H14.25C13.8358 9.75 13.5 9.41421 13.5 9ZM14.25 11.25C13.8358 11.25 13.5 11.5858 13.5 12C13.5 12.4142 13.8358 12.75 14.25 12.75H18.75C19.1642 12.75 19.5 12.4142 19.5 12C19.5 11.5858 19.1642 11.25 18.75 11.25H14.25ZM13.5 15C13.5 14.5858 13.8358 14.25 14.25 14.25H18.75C19.1642 14.25 19.5 14.5858 19.5 15C19.5 15.4142 19.1642 15.75 18.75 15.75H14.25C13.8358 15.75 13.5 15.4142 13.5 15ZM5.25 15.75C4.83579 15.75 4.5 16.0858 4.5 16.5C4.5 16.9142 4.83579 17.25 5.25 17.25H9.75C10.1642 17.25 10.5 16.9142 10.5 16.5C10.5 16.0858 10.1642 15.75 9.75 15.75H5.25Z" fill="currentColor"/>
                            </svg>
                            <span>Payments</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

    </div>
</aside>
