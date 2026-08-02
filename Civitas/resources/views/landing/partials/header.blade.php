<header>
    <nav id="nav" class="absolute group z-10 w-full border-b border-black/5 dark:border-white/5 lg:border-transparent">
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="relative flex flex-wrap items-center justify-between gap-6 py-3 md:gap-0 md:py-4">
                <div class="relative z-20 flex w-full justify-between md:px-0 lg:w-fit">
                    <a href="/" aria-label="logo" class="flex items-center space-x-2">
                        <div aria-hidden="true" class="flex space-x-1">
                            <div class="size-4 rounded-full bg-gray-900 dark:bg-white"></div>
                            <div class="h-6 w-2 bg-brand-500"></div>
                        </div>
                        <span class="text-2xl font-bold text-gray-900 dark:text-white">Civitas</span>
                    </a>
                    <div class="relative flex max-h-10 items-center lg:hidden">
                        <button aria-label="humburger" id="hamburger" class="relative -mr-6 p-6 active:scale-95 duration-300" onclick="toggleNav()">
                            <div aria-hidden="true" id="line" class="m-auto h-0.5 w-5 rounded bg-gray-950 transition duration-300 dark:bg-white origin-top"></div>
                            <div aria-hidden="true" id="line2" class="m-auto mt-2 h-0.5 w-5 rounded bg-gray-950 transition duration-300 dark:bg-white origin-bottom"></div>
                        </button>
                    </div>
                </div>
                <div id="navLayer" aria-hidden="true" class="fixed inset-0 z-10 h-screen w-screen origin-bottom scale-y-0 bg-white/70 backdrop-blur-2xl transition duration-500 dark:bg-gray-950/70 lg:hidden"></div>
                <div id="navlinks" class="invisible absolute top-full left-0 z-20 w-full origin-top-right translate-y-1 scale-90 flex-col flex-wrap justify-end gap-6 rounded-3xl border border-gray-100 bg-white p-8 opacity-0 shadow-2xl shadow-gray-600/10 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800 dark:shadow-none lg:visible lg:relative lg:flex lg:w-fit lg:translate-y-0 lg:scale-100 lg:flex-row lg:items-center lg:gap-0 lg:border-none lg:bg-transparent lg:p-0 lg:opacity-100 lg:shadow-none lg:dark:bg-transparent">
                    <div class="w-full text-gray-600 dark:text-gray-200 lg:w-auto lg:pr-4 lg:pt-0">
                        <div id="links-group" class="flex flex-col gap-6 tracking-wide lg:flex-row lg:gap-0 lg:text-sm">
                            <a href="#features" class="hover:text-brand-500 block transition dark:hover:text-white md:px-4"><span>Features</span></a>
                            <a href="#solution" class="hover:text-brand-500 block transition dark:hover:text-white md:px-4"><span>Services</span></a>
                            <a href="#techstack" class="hover:text-brand-500 block transition dark:hover:text-white md:px-4"><span>Tech Stack</span></a>
                            <a href="{{ route('login') }}" class="hover:text-brand-500 block transition dark:hover:text-white md:px-4"><span>Sign In</span></a>
                        </div>
                    </div>
                    <div class="mt-12 lg:mt-0">
                        <a href="{{ route('register') }}" class="relative flex h-9 w-full items-center justify-center px-4 before:absolute before:inset-0 before:rounded-full before:bg-brand-500 before:transition before:duration-300 hover:before:scale-105 active:duration-75 active:before:scale-95 sm:w-max">
                            <span class="relative text-sm font-semibold text-white">Get Started</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
function toggleNav() {
    const nav = document.getElementById('nav');
    const isActive = nav.dataset.state === 'active';
    nav.dataset.state = isActive ? '' : 'active';
    const line = document.getElementById('line');
    const line2 = document.getElementById('line2');
    if (!isActive) {
        line.style.transform = 'rotate(45deg) translateY(1.5px)';
        line2.style.transform = 'rotate(-45deg) translateY(-1px)';
    } else {
        line.style.transform = '';
        line2.style.transform = '';
    }
}
document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('#links-group a');
    links.forEach(link => {
        link.addEventListener('click', () => {
            const nav = document.getElementById('nav');
            nav.dataset.state = '';
            document.getElementById('line').style.transform = '';
            document.getElementById('line2').style.transform = '';
        });
    });
});
</script>
