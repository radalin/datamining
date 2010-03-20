<?php
require("includes.php");
$cellSize = 10;
?>
<html>
    <head>
        <!-- //Some required stylish things, not that much important... -->
        <style>
            div.dataCubeCell { width: <?=$cellSize?>px; height: <?=$cellSize?>px; display: table-cell; }
            div.dataCube { margin: 10px; }
            <?php
                for ($i = 0; $i < 10; $i++) {
                    echo 'div.allContainer' . $i . ' { position: absolute; left: ' . 260 * $i . 'px;}';
                }
            ?>
            div.meanContainer0 { position: absolute; left: 0px; top: 0px; }
            div.meanContainer1 { position: absolute; left: 0px; top: 230px; }
            div.meanContainer2 { position: absolute; left: 0px; top: 460px;}
            div.meanContainer3 { position: absolute; left: 260px; top: 0px;}
            div.meanContainer4 { position: absolute; left: 260px; top: 230px; }
            div.meanContainer5 { position: absolute; left: 260px; top: 460px;}
            div.meanContainer6 { position: absolute; left: 520px; top: 0px;}
            div.meanContainer7 { position: absolute; left: 520px; top: 230px; }
            div.meanContainer8 { position: absolute; left: 520px; top: 460px; }
            div.meanContainer9 { position: absolute; left: 780px; top: 0px; }
        </style>
    </head>
    <body>
        <?php
            //Create the DataReader and read the file...
            //We will create sample Examples from there...
            $matcher = new CubeMatcher("traicom.txt", "testcom.txt", 16);
            $matcher->trainMe();
            $matcher->drawMeans();
        ?>
    </body>
</html>