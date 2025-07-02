<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
        <style>
            .header {
                background-color: #F5F5DC; /* Cream color */
                color: #000; /* Default text color */
                padding: 15px 0;
                text-align: center;
                position: relative; /* For background squares */
                overflow: hidden; /* Clip background squares within header */
            }
            .header::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: repeating-squares(rgba(0, 0, 0, 0.05), 20px); /* Subtle dark squares */
                opacity: 0.8;
                z-index: -1; /* Behind the text */
            }
            @keyframes moveSquares {
                0% { background-position: 0 0; }
                100% { background-position: 20px 20px; }
            }
            .header::before {
                animation: moveSquares 10s linear infinite;
            }
            .u-name b {
                font-size: 1.5rem;
                color: green; /* Green color for ATTENDIFY and UGANDA INSTITUTE OF ICT */
            }
            #time {
                font-size: 1rem;
                color: #00008B; /* Dark blue color for time and date */
            }
            #navbtn {
                font-size: 1.5rem;
                color: #DAA520; /* Dark Yellow color for the navigation button */
                cursor: pointer;
                position: absolute;
                top: 10px;
                left: 10px;
            }
            center .u-name b {
                color: green; /* Ensure UGANDA INSTITUTE OF ICT is also green */
            }
            center span#time {
                color: #00008B; /* Ensure time and date in center are dark blue */
            }
        </style>
    </head>
    <header class="header">
        <h2 class="u-name"><b>ATTENDIFY</b></h2>
        <center>
            <h2 class="u-name"><b>UGANDA INSTITUTE OF ICT</b><br></h2>
            <?php
            date_default_timezone_set('Africa/Kampala');

            $dates = date("d, F, Y");
            $current = date("h:i:s A");
            ?>
            <span id="time"><?php echo $dates; ?><br><?php echo $current; ?></span>
        </center>
        <label for="checkbox">
            <i id="navbtn" class="fa fa-bars" aria-hidden="true"></i>
        </label>
        <script>
            function updateTime() {
                const now = new Date();
                const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
                const currentTime = now.toLocaleTimeString('en-US', options);

                document.getElementById('time').innerHTML = `${currentTime}<br><?php echo $dates; ?>`;
            }

            setInterval(updateTime, 1000); // Update time every second
        </script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
    </header>