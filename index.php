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
                    echo 'div.allContainer' . $i . '{ position: absolute; left: ' . 260 * $i . 'px;}';
                }
            ?>
        </style>
    </head>
    <body>
        <?php
            //Create the DataReader and read the file...
            //We will create sample Examples from there...
            $reader = new DataCubeReader("traicom.txt", 16);
            $dataCubes = $reader->read();
            for ($i = 0; $i < count($dataCubes); $i++) {
                $dataCubes[$i]->drawMean();
            }
        ?>
    </body>
</html>