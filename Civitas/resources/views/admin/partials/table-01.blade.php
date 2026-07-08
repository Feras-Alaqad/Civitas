<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6">
    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Orders</h3>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">
                <svg class="stroke-current fill-white dark:fill-gray-800" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2.29004 5.90393H17.7067" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M17.7075 14.0961H2.29085" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12.0826 3.33331C13.5024 3.33331 14.6534 4.48431 14.6534 5.90414C14.6534 7.32398 13.5024 8.47498 12.0826 8.47498C10.6627 8.47498 9.51172 7.32398 9.51172 5.90415C9.51172 4.48432 10.6627 3.33331 12.0826 3.33331Z" fill="" stroke="" stroke-width="1.5"/>
                    <path d="M7.91745 11.525C6.49762 11.525 5.34662 12.676 5.34662 14.0959C5.34661 15.5157 6.49762 16.6667 7.91745 16.6667C9.33728 16.6667 10.4883 15.5157 10.4883 14.0959C10.4883 12.676 9.33728 11.525 7.91745 11.525Z" fill="" stroke="" stroke-width="1.5"/>
                </svg>
                Filter
            </button>
            <button class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">See all</button>
        </div>
    </div>
    <div class="w-full overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-gray-100 border-y dark:border-gray-800">
                    <th class="py-3"><div class="flex items-center"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Products</p></div></th>
                    <th class="py-3"><div class="flex items-center"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Category</p></div></th>
                    <th class="py-3"><div class="flex items-center"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Price</p></div></th>
                    <th class="py-3"><div class="flex items-center col-span-2"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Status</p></div></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ([
                    ['name' => 'Macbook pro 13"', 'variant' => '2 Variants', 'cat' => 'Laptop', 'price' => '$2399.00', 'status' => 'Delivered', 'statusClass' => 'success'],
                    ['name' => 'Apple Watch Ultra', 'variant' => '1 Variants', 'cat' => 'Watch', 'price' => '$879.00', 'status' => 'Pending', 'statusClass' => 'warning'],
                    ['name' => 'iPhone 15 Pro Max', 'variant' => '2 Variants', 'cat' => 'SmartPhone', 'price' => '$1869.00', 'status' => 'Delivered', 'statusClass' => 'success'],
                    ['name' => 'iPad Pro 3rd Gen', 'variant' => '2 Variants', 'cat' => 'Electronics', 'price' => '$1699.00', 'status' => 'Canceled', 'statusClass' => 'error'],
                    ['name' => 'Airpods Pro 2nd Gen', 'variant' => '1 Variants', 'cat' => 'Accessories', 'price' => '$240.00', 'status' => 'Delivered', 'statusClass' => 'success'],
                ] as $item)
                <tr>
                    <td class="py-3">
                        <div class="flex items-center">
                            <div class="flex items-center gap-3">
                                <div class="h-[50px] w-[50px] overflow-hidden rounded-md">
                                    <img src="{{ asset('images/product/product-0' . $loop->iteration . '.jpg') }}" alt="Product" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $item['name'] }}</p>
                                    <span class="text-gray-500 text-theme-xs dark:text-gray-400">{{ $item['variant'] }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3"><div class="flex items-center"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $item['cat'] }}</p></div></td>
                    <td class="py-3"><div class="flex items-center"><p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $item['price'] }}</p></div></td>
                    <td class="py-3">
                        <div class="flex items-center">
                            <p class="rounded-full bg-{{ $item['statusClass'] }}-50 px-2 py-0.5 text-theme-xs font-medium text-{{ $item['statusClass'] }}-600 dark:bg-{{ $item['statusClass'] }}-500/15 dark:text-{{ $item['statusClass'] }}-500">{{ $item['status'] }}</p>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
