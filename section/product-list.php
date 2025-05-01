<?php
include '_products_data.php';

?>
<section id="product-list" class="section products min-h-screen justify-center" data-aos="fade-up">
    <h2>Check Our Products</h2>
    <div class="container flex-row mobile-column vert-top gap-2 justify-between" data-aos="fade-up" data-aos-delay="200">
        <?php
        for($i=1; $i<=count($product); $i++) {
            if($i>3) break(1);
            $p=$product[$i];
            echo "
                <div class=\"col1-3 card row1-3 black-pad\" data-aos=\"fade-up\" data-aos-delay=\"300\">
                    <a href='/products/$i' class='product product-thumb' data-id='$i'>
                        <picture>
                            <source srcset=\"". $p['image']['desk'] ."\" media=\"(min-width: 1024px)\">
                            <source srcset=\"". $p['image']['pad'] ."\" media=\"(min-width: 768px)\">
                            <img src=\"". $p['image']['mob'] ."\" alt=\"". $p['title'] ."\" class=\"product-main-image\">
                        </picture>
                    </a>                            
                    <div class=\"description\">
                        <h3>".$p['title']."</h3>
                        <p>".$p['short-description']."</p>
                    </div>
                </div>
            ";
        }
        ?>
    </div>
</section>
