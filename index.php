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
            div.meanContainer0 { position: absolute; left: 0px; top: 40px; }
            div.meanContainer1 { position: absolute; left: 0px; top: 270px; }
            div.meanContainer2 { position: absolute; left: 0px; top: 500px;}
            div.meanContainer3 { position: absolute; left: 260px; top: 40px;}
            div.meanContainer4 { position: absolute; left: 260px; top: 270px; }
            div.meanContainer5 { position: absolute; left: 260px; top: 500px;}
            div.meanContainer6 { position: absolute; left: 520px; top: 40px;}
            div.meanContainer7 { position: absolute; left: 520px; top: 270px; }
            div.meanContainer8 { position: absolute; left: 520px; top: 500px; }
            div.meanContainer9 { position: absolute; left: 780px; top: 40px; }
            div.resultsList { position: absolute; top: 40px; width: 300px; height: 400px; overflow-x: hidden; overflow-y: scroll; }
            #confusionMatrix { position: absolute; top: 40px; left: 320px; }
        </style>
    </head>
    <body>
        <!-- Show A simple GUI -->
        <div>
            Please Select an action:
            <select name="action" onChange="document.location='index.php?action=' + this.value">
                <option value=""></option>
                <option value="drawMean">Draw Means For Training Set</option>
                <option value="meanMatch">Match Means and Draw Confusion Matrix</option>
                <option value="totalMatch">Match Everything and Draw Confusion Matrix</option>
            </select>
        </div>
        <?php
            if ($_GET) {
                //Create the DataReader and read the file...
                //We will create sample Examples from there...
                $matcher = new CubeMatcher("traicom.txt", "testcom.txt", 16);
                $matcher->trainMe();
                switch ($_GET['action']) {
                    case "drawMean":
                        $matcher->drawMeans();
                        break;
                    case "meanMatch":
                            $matcher->matchTestDataWithMeans();
                        break;
                    case "totalMatch":
                            $matcher->matchTestDataWithTraining();
                    break;
                    default:
                        echo "No Action is Selected, Please Select A One!";
                }
            }
        ?>
    </body>
</html>
