<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Riphean Marble private limited</title>
    <meta name="color-scheme" content="dark">
    <style>

        .background_black_stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000000;
            opacity: 1;
            transition: opacity 1s ease-out;
            z-index: 999999999999999999999;
        }

        .star {
            position: absolute;
            background: #ffd752;
            border-radius: 50%;
            animation: twinkle 1.5s infinite alternate;
        }

        @keyframes twinkle {
            from { opacity: 0.2; }
            to { opacity: 1; }
        }

        #logo {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            max-width: 70vw;
            z-index: 999999999999999999999;
        }
        #logo img{
            width: 100%;
        }

    </style>
</head>
<body>



    <div class="background_black_stars">



    </div>




<script>
    window.onload = function () {
        document.body.classList.add('loaded');

    };


    const starsContainer = document.querySelector('.background_black_stars');
    for (let i = 0; i < 200; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.width = Math.random() * 3 + 'px';
        star.style.height = star.style.width;
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDelay = Math.random() * 2 + 's';
        starsContainer.appendChild(star);
    }



</script>






    <div id="logo" style="">
        <img src="/images/rm_logo_2.svg" alt="logo">
    </div>

</body>
</html>
