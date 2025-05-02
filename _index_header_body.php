<header id="main-header" class=" z-50">



    <a href="#"><img id="logospiral"
                     onclick="window.location.href='/'"
                     class="filter-outline-black" class="logo" src="/images/rm-symbol.svg" alt="Riphean Marble Logo" data-aos="zoom-in"></a>

<!--    <svg id="logospiral"
         onclick="window.location.href='/'"
         class="filter-outline-black"
            xmlns="http://www.w3.org/2000/svg"
            width="186.503pt"
            height="138.041pt"
            viewBox="0 -138.041 186.503 138.041"
            fill="none"
            stroke="black">
        <defs>
            <linearGradient id="golden_LinearGradient" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="0" y2="182.032" gradientTransform="translate(313.946 205.772)">
                <stop offset="0" stop-color="#c18e61"/>
                <stop offset="0.153000012040138" stop-color="#c49366"/>
                <stop offset="0.346400022506714" stop-color="#cb9f76"/>
                <stop offset="0.561500012874603" stop-color="#d7b590"/>
                <stop offset="0.790599942207336" stop-color="#e7d4b4"/>
                <stop offset="1" stop-color="#f9f3db"/>
            </linearGradient>
        </defs>

    </svg>
-->

    <div onclick="window.location.href='/'" id="logotext" class="filter-outline-black  golden-text-bgr" style="font-family: 'Century Gothic', sans-serif">RIPHEAN MARBLE</div>


    <div class="menu">


        <div class="hamburger box-shadow" id="hamburger">
            <div></div>
            <div></div>
            <div></div>
            <span class="mob-off">MENU</span>
        </div>
        <nav style="" id="menu">
            <header class="semantic-only">MENU</header>
            <div class="close" id="menu-close"></div>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/about">About Us</a></li>
                <li><a href="/products">Products</a></li>
                <li><a href="/categories">Browse Categories</a></li>
                <li><a href="/contacts">Contact Us</a></li>
            </ul>
        </nav>

        <script>
            // hamburger
            let h,m,c;
            function o(){m.classList.add('open');}
            function x(){m.classList.remove('open');}
            h=document.getElementById('hamburger');
            m=document.getElementById('menu');
            c=document.getElementById('menu-close');
            h.addEventListener('click',o);
            c.addEventListener('click',x);

            window.addEventListener('scroll', () => {
                const header = document.getElementById('main-header');
                if (window.scrollY > 200) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

        </script>
    </div>

</header>

<script>
    function shrinkHeaderOnScroll() {
        const header = document.querySelector('header');
        const scrollY = window.scrollY;
        const viewportHeight = window.innerHeight;
        if (scrollY > 0) {
            header.style.height = Math.max(60, 250 - scrollY) + 'px';
        } else {
            header.style.height = 250 + 'px';
        }
    }
    window.addEventListener('scroll', shrinkHeaderOnScroll);
    shrinkHeaderOnScroll();
</script>