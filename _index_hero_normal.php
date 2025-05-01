<?php
$folder = "images/heroslide/";
// read first 3 files from folder
$files = scandir($folder);
// remove . and .. from array
$files = array_diff($files, array('.', '..'));
// select 3 random files
$rand_keys = array_rand($files, 3);
?>

<section id="hero1" class="min-h-screen relative isolate flex flex-col gap-5 overflow-clip"
         style="background-image: url('images/heroslide/<?=$files[array_pop($rand_keys)]?>')" data-aos="fade-up">

    <div class="hero-desc slide-desc-norm flex-1 px-7 relative flex-col" data-aos="fade-up" data-aos-delay="200">
        <div class="overlap">
            <p class="att1"><span class="outline_black">History in every slab.</span></p>
        </div>
        <div class="hero-h1 font-bold align-middle filter-outline-black golden-text-bgr flex line-1 flex-row-mobile">
            <div class="extrabig condensed_style" style="width: 46%">1.6</div>
            <div style="margin-left: 10px; display: flex; font-size: 1.2em; text-transform: uppercase; line-height: 1.2;">
                billion<br>years
            </div>
        </div>

        <div class="bottom-text caption-part overlap" data-aos="fade-up" data-aos-delay="400">
            <div class="atb1">
                <p class="pb-7"><span class="outline_black">Nature's Masterpiece</span></p>
            </div>
        </div>
        <div class="slider-nav w-100 my-auto" data-aos="fade-up" data-aos-delay="100">
            <nav class="inner-menu-wrap flex font-medium text-sm">
                <a href="#slide-1" class="inner-menu golden-background pointer-events-auto slide-button-1">Pro<wbr>tero<wbr>zoic</a>
            </nav>
        </div>
    </div>
</section>

<section id="hero2" class="min-h-screen relative isolate flex flex-col gap-5 overflow-clip"
         style="background-image: url('images/heroslide/<?=$files[array_pop($rand_keys)]?>')" data-aos="fade-up">

    <div class="hero-desc slide-desc-norm flex-1 px-7 flex-col" data-aos="fade-up" data-aos-delay="100">
        <div class="overlap">
            <p class="att2"><span class="outline_black">Exclusive Stromatolite Marble from the South Urals for your most prestigious projects.</span></p>
        </div>
        <h1 class="hero-h1 font-bold inline align-middle filter-outline-black golden-text-bgr" data-aos="fade-up" data-aos-delay="300">Paleo<wbr>protero<wbr>zoic</h1>

        <div class="bottom-text caption-part overlap" data-aos="fade-up" data-aos-delay="400">
            <div class="atb2">
                <span class="block overflow-clip"><span class="block uppercase font-medium tracking-widest mb-4 -up"><span class="outline_black"><b>Experience</b> Elegance</span></span></span>
                <p class="pb-7">Gift From Past <em>to Present</em></p>
            </div>
        </div>

        <div class="slider-nav w-100 my-auto" data-aos="fade-up" data-aos-delay="50">
            <nav class="inner-menu-wrap flex font-medium text-sm">
                <a href="#slide-2" class="inner-menu golden-background pointer-events-auto slide-button-2">Paleo<wbr>protero<wbr>zoic</a>
            </nav>
        </div>
    </div>
</section>

<section id="hero3" class="min-h-screen relative isolate flex flex-col gap-5 overflow-clip justify-center"
         style="background-image: url('images/heroslide/<?=$files[array_pop($rand_keys)]?>')" data-aos="fade-up">

    <div class="hero-desc slide-desc-norm flex-1 px-7 flex-col" data-aos="fade-up" data-aos-delay="200">
        <div class="overlap">
            <p class="att3"><span class="outline_black">Transform spaces with timeless elegance from the heart of Planet.</span></p>
        </div>
        <h1 class="hero-h1 font-bold inline align-middle filter-outline-black golden-text-bgr" data-aos="fade-up" data-aos-delay="30">Your Legacy,</h1>

        <div class="bottom-text caption-part overlap" data-aos="fade-up" data-aos-delay="300">
            <div class="atb3">
                <span class="block uppercase font-medium tracking-widest mb-4 -up outline_black">Set in Stone</span>
                <p class="pb-7 outline_black">Luxury Beyond</p>
            </div>
        </div>

        <div class="slider-nav w-100 my-auto" data-aos="fade-up" data-aos-delay="200">
            <nav class="inner-menu-wrap flex font-medium text-sm">
                <a href="#slide-3" class="inner-menu golden-background pointer-events-auto slide-button-3">Sta<wbr>the<wbr>rian</a>
            </nav>
        </div>
    </div>
</section>
