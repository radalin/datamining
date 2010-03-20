<?php require("includes.php"); ?>
<html>
    <head>
        <style>
            div.dataCubeCell {
                width: 5px;
                height: 5px;
                display: inline;
            }
            div.dataCube {
                margin: 10px;
            }
        </style>
    </head>
    <body>
        <?php
            //Create the DataReader and read the file...
            //We will create sample Examples from there...
            $reader = new DataCubeReader("traicom.txt");
            $dataCubes = $reader->read();
            //Now calculate the mean and draw them all
            //for ($i = 0; $i < count($dataCubes); $i++) {
            //    $dataCubes->drawMean();
            //}
            var_dump($dataCubes);
            $dataCubes[0]->drawAll();
        ?>
    </body>
</html>