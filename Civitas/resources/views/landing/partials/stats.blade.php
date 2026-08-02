<div id="solution">
    <div class="max-w-7xl mx-auto px-6 md:px-12">
        <div class="space-y-6 justify-between text-gray-600 md:flex flex-row-reverse md:gap-6 lg:gap-12 lg:items-center">
            <div class="md:5/12 lg:w-1/2">
                <img src="{{ asset('landing/images/pie.svg') }}" alt="image" loading="lazy" class="w-full" />
            </div>
            <div class="md:7/12 lg:w-1/2">
                <h2 class="text-3xl font-bold text-gray-900 md:text-4xl dark:text-white">Processing 4 million records without compromise</h2>
                <p class="my-8 text-gray-600 dark:text-gray-300">Most systems slow to a crawl when datasets cross the million-record threshold. This system was designed from day one for that scale — every query is optimized, every process is async, and every component is horizontally scalable.</p>
                <div class="divide-y space-y-4 divide-gray-100 dark:divide-gray-800">
                    <div class="mt-8 flex gap-4 md:items-center">
                        <svg class="size-6 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-white">Sub-300ms Query Performance</h3>
                            <p class="text-gray-600 dark:text-gray-300">Database indexes, query optimization, and connection pooling ensure every search returns in milliseconds regardless of concurrent load.</p>
                        </div>
                    </div>
                    <div class="pt-4 flex gap-4 md:items-center">
                        <svg class="size-6 shrink-0 text-brand-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/></svg>
                        <div>
                            <h3 class="font-semibold text-gray-700 dark:text-white">Memory-Safe Bulk Processing</h3>
                            <p class="text-gray-600 dark:text-gray-300">CSV files are streamed in chunks via a Celery + Redis task queue. No page timeout, no memory spike, no server crash — even with hundreds of thousands of rows.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
