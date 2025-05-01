<?php
//  log all error on screen
require '_config.php';




$header = [
    'title' => 'Quarry',
    'keywords' => 'Quarry',
    'meta-title' => 'Quarry',
    'description' => 'Quarry'
];
$page_name = 'quarry';

include '_header.php';


?>

<section id="extra-about" class="min-h-screen relative isolate flex flex-col gap-5 supports-sda:pointer-events-none overflow-clip"
         style="padding: 0 17vw 10vh; background-image: url('./images/trucks-in-a-marble-quarry---brown-marble-slab---op.webp'); background-size: cover; justify-content: flex-end; background-attachment: fixed"
         data-aos="fade-up">
    <div class="flex-row vert-top gap-1_5 container" data-aos="fade-up" data-aos-delay="200">
        <h1 class="main-title filter-outline-black golden-text-bgr" data-aos="fade-up" data-aos-delay="100">Paleoproterozoic Marble Quarry</h1>


    </div>
</section>


<section class="min-h-screen flex flex-col gap-5 justify-center">
    <h2 class="outline_black">Quarry</h2>
    <img src="images/dark-brown-marble-slab--quarry-2.webp">
    <div class="description">
        <h3 class="outline_black mb-4" data-aos="fade-up" data-aos-delay="300">Geological Significance</h3>
        <p class="outline_black mb-4" data-aos="fade-up" data-aos-delay="400">Riphean Marble's origins trace back to the Proterozoic Eon, specifically the Statherian Period of
            the Paleoproterozoic Era. Our marble slabs are among the oldest on Earth, offering unmatched rarity and beauty.</p>
        <p class="outline_black" data-aos="fade-up" data-aos-delay="500">Featuring textures formed by ancient Stromatolites and unique hues of grey, dark green, and dark red, Riphean Marble's distinctive charm is unparalleled.</p>
    </div>
</section>
<?php

include '_footer.php';
?>
