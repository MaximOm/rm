<?php
//  log all error on screen
require '_config.php';




$header = [
    'title' => 'Mission',
    'keywords' => 'Mission',
    'meta-title' => 'Mission',
    'description' => 'Mission',
    'stylesheets' => [
        'assets/css/aos.css'
        ],
];
$page_name = 'mission';
$footer = [
    'scripts' => [
            'assets/js/aos.js'
    ],
];
include '_header.php';


?>

<section id="extra-about" class="min-h-screen relative isolate flex flex-col gap-5 supports-sda:pointer-events-none overflow-clip"
         style="padding: 0 17vw 10vh; background-image: url('./images/bgr/rm21.jpg'); background-size: cover; justify-content: flex-end; background-attachment: fixed"
         data-aos="fade-up">
    <div class="flex-row vert-top gap-1_5 container" data-aos="fade-up" data-aos-delay="200">
        <h1 class="main-title filter-outline-black golden-text-bgr" data-aos="fade-up" data-aos-delay="100">Riphean Marble's Mission</h1>


    </div>
</section>


<section class="min-h-screen flex flex-col gap-5 justify-center">
    <h2 class="outline_black">Mission</h2>
    <p class="outline_black mb-4">At Riphean Marble, our mission is to unearth the beauty of the past and bring it to life in the present.</p>
    <p class="outline_black">We're dedicated to sourcing the finest, rarest marble from around the world and crafting it into breathtaking works of art that inspire and elevate.</p>
    <div class="description">
        <h3 class="semantic-only">Our Promise</h3>
        <p class="outline_black mb-4">We promise to deliver exceptional quality, unparalleled craftsmanship, and a passion for perfection in every piece we create.</p>
    </div>
</section>

<section class="min-h-screen flex flex-col gap-5 justify-center">
    <h2 class="outline_black">Our Vision</h2>
    <p class="outline_black mb-4">At Riphean Marble, we envision a world where beauty and elegance are within reach, where every space is a reflection of the owner's unique story and style.</p>
    <p class="outline_black">We see ourselves as curators of the finest marble, carefully selecting and crafting each piece to bring joy and inspiration to those who experience it.</p>
    <div class="description">
        <h3 class="semantic-only">Join Our Journey</h3>
        <p class="outline_black mb-4">Join us on our mission to uncover the beauty of the past and bring it to life in the present. Let us help you create a space that tells your story and inspires your soul.</p>
    </div>
</section>

<section class="timeline-section">
    <h2 class="outline_black">Our Journey</h2>
    <div class="timeline-container">
        <div class="timeline">
            <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000">
                <div class="timeline-date">2010</div>
                <div class="timeline-content">
                    <h3 class="semantic-only">Founding Year</h3>
                    <p class="outline_black">Riphean Marble was founded with a vision to bring the beauty of marble to the world.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="500">
                <div class="timeline-date">2015</div>
                <div class="timeline-content">
                    <h3 class="semantic-only">First Collection Launch</h3>
                    <p class="outline_black">We launched our first collection of marble products, featuring unique and exclusive designs.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="1000">
                <div class="timeline-date">2020</div>
                <div class="timeline-content">
                    <h3 class="semantic-only">Expansion into New Markets</h3>
                    <p class="outline_black">We expanded our reach into new markets, partnering with top designers and architects to bring our marble products to a wider audience.</p>
                </div>
            </div>
            <div class="timeline-item" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="1500">
                <div class="timeline-date">2025</div>
                <div class="timeline-content">
                    <h3 class="semantic-only">Sustainability Initiative</h3>
                    <p class="outline_black">We launched a sustainability initiative, committing to environmentally responsible practices in our sourcing and production processes.</p>
                </div>
            </div>
        </div>
    </div>
</section>





<?php

include '_footer.php';
?>
