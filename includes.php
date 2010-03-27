<?php


/**
 * Reads cubes from a file and creates appropriate DataCube objects...
 */
class DataCubeReader
{
    private $_traFile;
    
    private $_testFile;
    
    private $_cubeSize;
    
    public function __construct($cubeSize = 16, $trainingFile = "traicom.txt", $testFile = "testcom.txt")
    {
        $this->_traFile = $trainingFile;
        $this->_testFile = $testFile;
        $this->_cubeSize = $cubeSize;
    }
    
    /**
     *
     * @return array<DataCubeList>
     */
    public function readTrainingData()
    {
        //Read it line by line...
        $fileContents = split("\n", file_get_contents($this->_traFile));
        //Split from lines...
        $count = count($fileContents);
        $cubeList = array();
        //Create all the possible number DataCubeLists. Samples for "0" (zero) will be in the index "0"
        for ($i = 0; $i < 10; $i++) {
            $cubeList[$i] = new DataCubeList($i, array()); //Add an empty cube list now, later we will add them...
        }
        for ($i = 0; $i < $count; $i += $this->_cubeSize + 1) { //Increase the count 17 by 17, so read the cubes as block...
            $numberValue = trim($fileContents[$i + $this->_cubeSize]);
            $cubeItems = array();
            for ($x = 0; $x < $this->_cubeSize; $x++) {
                $row = array();
                for ($y = 0; $y < 16; $y++) {
                    $row[] = $fileContents[$i + $x][$y]; //Just add the string value to the array because I can use that string as an array in PHP...
                }
                $cubeItems[] = $row;
            }
            $cube = new DataCube($numberValue, $cubeItems);
            $cubeList[$numberValue]->add($cube);
            $cube = null;
        }
        return $cubeList;
    }
    
    public function readTestData()
    {
        //Read it line by line...
        $fileContents = split("\n", file_get_contents($this->_testFile));
        //Split from lines...
        $count = count($fileContents);
        $cubeList = new DataCubeList(null, array());
        for ($i = 0; $i < $count; $i += $this->_cubeSize + 1) { //Increase the count 17 by 17, so read the cubes as block...
            $numberValue = trim($fileContents[$i + $this->_cubeSize]);
            $cubeItems = array();
            for ($x = 0; $x < $this->_cubeSize; $x++) {
                $row = array();
                for ($y = 0; $y < 16; $y++) {
                    $row[] = $fileContents[$i + $x][$y]; //Just add the string value to the array because I can use that string as an array in PHP...
                }
                $cubeItems[] = $row;
            }
            $cube = new DataCube($numberValue, $cubeItems);
            $cubeList->add($cube);
            $cube = null;
        }
        return $cubeList;
    }
}

class CubeMatcher
{
    private $_reader;
    
    /**
     * @var array<DataCubeList>
     */
    private $_dataCubeList;
    
    /**
     * @var DataCubList
     */
    private $_merger;
    
    public function __construct($trainFile, $testFile, $cubeSize)
    {
        $this->_reader = new DataCubeReader($cubeSize, $trainFile, $testFile);
    }
    
    public function trainMe()
    {
        $this->_dataCubeList = $this->_reader->readTrainingData();
    }
    
    public function drawMeans()
    {
        for ($i = 0; $i < count($this->_dataCubeList); $i++) {
            $this->_dataCubeList[$i]->drawMean();
        }
    }
    
    private function _mergeAllCubes()
    {
        $this->_merger = new DataCubeList(null, array());
        for ($i = 0; $i < count($this->_dataCubeList); $i++) {
            for ($j = 0; $j < $this->_dataCubeList[$i]->count(); $j++) {
                $this->_merger->add($this->_dataCubeList[$i]->get($j));
            }
        }
        return $this->_merger;
    }
    
    public function matchTestDataWithTraining($nearest = 3)
    {
        if ($this->_dataCubeList == null) {
            $this->trainMe();
        }
        echo "<div class='resultsList'>";
        $testCubes = $this->_reader->readTestData();
        $this->_mergeAllCubes();
        $testCount = $testCubes->count();
        $confusionMatrix = array();
        for ($i = 0; $i < 10; $i++) {
            $confusionMatrix[$i] = array(); //Create a two dimensional array, Don't remember a better implementation right now, don't care either...
            for ($j = 0; $j < 10; $j++) {
                $confusionMatrix[$i][$j] = 0;
            }
        }
        $failure = 0;
        for ($i = 0; $i < $testCount; $i++) {
            $value = $testCubes->get($i)->matchWithNearest($this->_merger, $nearest);
            if ($value == $testCubes->get($i)->numberValue) {
                echo $i . "th, Success, we have for {$testCubes->get($i)->numberValue} and {$value}.<br>";
            } else {
                echo $i . "th, Confused {$testCubes->get($i)->numberValue} with {$value}<br />";
                $failure++;
            }
            flush();
            $confusionMatrix[$testCubes->get($i)->numberValue][$value]++;
        }
        echo "</div>";
        $this->drawConfusionMatrix($confusionMatrix, "Total Failure: " . $failure . "/" . $testCount);
    }
    
    public function matchTestDataWithMeans()
    {
        if ($this->_dataCubeList == null) {
            $this->trainMe();
        }
        echo "<div class='resultsList'>";
        $testCubes = $this->_reader->readTestData();
        $testCount = $testCubes->count();
        $confusionMatrix = array(); //first dimension is what it should be, while the other is what actually is...
        for ($i = 0; $i < 10; $i++) {
            $confusionMatrix[$i] = array(); //Create a two dimensional array, Don't remember a better implementation right now, don't care either...
            for ($j = 0; $j < 10; $j++) {
                $confusionMatrix[$i][$j] = 0;
            }
        }
        $failure = 0;
        $values = array();
        for ($i = 0; $i < $testCount; $i++) {
            //Match with all means...
            for ($j = 0; $j < count($this->_dataCubeList); $j++) {
                $values[$j] = $testCubes->get($i)->matchWith($this->_dataCubeList[$j]->getMean());
            }
            asort($values); //Sort it by reverse...
            foreach ($values as $key => $val) {
                if ($key == $testCubes->get($i)->numberValue) {
                    echo $i . "th, Success, we have for {$testCubes->get($i)->numberValue} and {$key}.<br>";
                } else {
                    echo $i . "th, Confused {$testCubes->get($i)->numberValue} with $key<br />";
                    $failure++;
                }
                $confusionMatrix[$testCubes->get($i)->numberValue][$key]++;
                break; //Quit after the first as there is no need to go on, because the one with most points is the perfect match!
            }
            flush();
        }
        echo "</div>";
        $this->drawConfusionMatrix($confusionMatrix, "Total Failure: " . $failure . "/" . $testCount);
    }
    
    public function drawConfusionMatrix($matrix, $footerStr)
    {
        echo '<div id="confusionMatrix">
                <table cellpadding="2" cellspacing="2" border="1">
                    <tr>
                        <th width="30">#</th>
        ';
        for ($i = 0; $i < 10; $i++) {
            echo "<th width='30'>$i</th>"; //Show the header values...
        }
        echo "</tr>";
        for ($i = 0; $i < count($matrix); $i++) {
            echo "<tr><td align='center'><strong>$i</strong></td>";
            for ($j = 0; $j < count($matrix[$i]); $j++) {
                echo "<td align='center'>{$matrix[$i][$j]}</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
        echo $footerStr;
        echo '</div>';
    }
}

class DataCubeList
{
    private $_cubeArray = array();
    
    private $_numberValueForSamples = null;
    
    /**
     * @var MeanDataCube
     */
    private $_meanDataCube = null;
    
    public function __construct($number, $cubes)
    {
        $this->_numberValueForSamples = $number;
        $this->_cubeArray = $cubes;
    }
    
    public function calculateMeanDataCube()
    {
        //Now create a sum of all the samples we have...
        $this->_meanDataCube = new MeanDataCube($this->_numberValueForSamples, $this->_cubeArray[0]->dataArray); //Add the first cube to the list...
        for ($i = 1; $i < $this->count(); $i++) { //As I have added the first, keep going from the index with 1...
            $this->_meanDataCube->addToSum($this->_cubeArray[$i]); //This will calculate the rest...
        }
        $this->_meanDataCube->finalize();
    }
    
    //Below is the less important stuff some simple getters and setters and drawers etc...
    
    public function get($index)
    {
        return $this->_cubeArray[$index];
    }
    
    public function count()
    {
        return count($this->_cubeArray);
    }
    
    public function add(DataCube $cube) {
        $this->_cubeArray[] = clone $cube;
    }
        
    public function drawAll()
    {
        echo '<div class="allContainer' . $this->_numberValueForSamples . '">Total Number of Samples: ' . $this->count();
        for ($i = 0; $i < $this->count(); $i++) {
            $this->_cubeArray[$i]->draw();
        }
        echo '</div>';
    }
    
    public function getMean()
    {
        if ($this->_meanDataCube == null) {
            $this->calculateMeanDataCube();
        }
        return $this->_meanDataCube;
    }
        
    public function drawMean()
    {
        if ($this->_meanDataCube == null) {
            $this->calculateMeanDataCube();
        }
        $this->_meanDataCube->draw();
    }
}

class DataCube
{
    public $dataArray = array();
    
    public $numberValue = null;
    
    public function __construct($number = null, $array = array())
    {
        $this->dataArray = $array;
        $this->numberValue = $number;
    }
        
    protected function _findCellColor($x, $y)
    {
        //Every 1 (one) is black and every 0 (zero) is white
        if ($this->dataArray[$x][$y] == 1) {
            return "#000000"; //Return black...
        }
        return "#FFFFFF"; //Return white...
    }
    
    public function matchWith(DataCube $cube)
    {
        //Return the heuristic number...
        //The mathic numbers gives us a point, higher points means, higher matching the one with highest match num is the winner...
        $heuristic = 0;
        for ($i = 0; $i < count($this->dataArray); $i++) {
            for ($j = 0; $j < count($this->dataArray[$i]); $j++) {
                //FIXME: Turn this to a proper algortihm...
                $heuristic += ($cube->dataArray[$i][$j] - $this->dataArray[$i][$j]) * ($cube->dataArray[$i][$j] - $this->dataArray[$i][$j]);
                /*
                if ($this->dataArray[$i][$j] > 0 && $cube->dataArray[$i][$j] > 0) { //If there is a value in both of them...
                    $heuristic += $cube->dataArray[$i][$j] * $cube->dataArray[$i][$j];
                }
                */
            }
        }
        return $heuristic;
    }
    
    /**
     * @param int $k the closest neighbor which I will care about...
     * @return int the closes number value.
     */
    public function matchWithNearest(DataCubeList $cubeList, $k)
    {
        $heuristics = array();
        for ($i = 0; $i < $cubeList->count(); $i++) {
            $heuristics[$i] = $this->matchWith($cubeList->get($i)); //Calculate the heuristics for the DataCube thingy...
        }
        //Now match with $k nearest neighbors...
        //Sort the array as decreasing while keeping the index values (key-value associations) where I will get to the DataCube from $cubeList...
        asort($heuristics);
        //So here is the easy part, what are the values of top $k elements...
        for ($i = 0; $i < 10; $i++) {
            $numbers[$i] = 0; //A baaaaaaaad code comes here....
        }
        $i = 1;
        foreach ($heuristics as $index => $heuristic) {
            if ($i > $k) {
                break;
            }
            $numbers[$cubeList->get($index)->numberValue]++;
            $i++;
        }
        echo "[";
        foreach ($numbers as $key => $val) {
            echo "$key => $val, ";
        }
        echo "] ";
        //Now sort the numbers array without maintaining the key-value association
        arsort($numbers);
        //get the keys
        $numVals = array_keys($numbers);
        return $numVals[0]; //Returns the number with lowest value...
    }
  
    //The below is the less important stuff, the drawing implementation etc...
    
    public function draw()
    {
        echo '<div class="dataCube"> This Number is: ' . $this->numberValue . '<br />';
        for ($i = 0; $i < count($this->dataArray); $i++) {
            echo '<div class="dataCubeRow">';
            for ($j = 0; $j < count($this->dataArray[$i]); $j++) {
                $color = $this->_findCellColor($i, $j);
                $this->_drawCellWithColor($color, $this->dataArray[$i][$j]);
            }
            echo '</div>';
        }
        echo '</div><br />';
    }
    
    protected function _drawCellWithColor($color, $text = null)
    {
        echo '<div class="dataCubeCell" style="background-color:' . $color . '"></div>';
    }
}

class MeanDataCube extends DataCube
{
    private $_sampleSize;
    
    private $_isFinalized = false;
    
    public function __construct($number, $array)
    {
        parent::__construct($number, $array);
        $this->_sampleSize = 1;
    }
    
    public function addToSum(DataCube $cube)
    {
        if ($this->_isFinalized) {
            throw new Exception("You can't add any more data cubes to this Mean Cube as it's finalized");
        }
        $this->_sampleSize++; //Increase the sample size...
        for ($x = 0; $x < count($this->dataArray); $x++) {
            for ($y = 0; $y < count($this->dataArray[$x]); $y++) {
                $this->dataArray[$x][$y] = $this->dataArray[$x][$y] + $cube->dataArray[$x][$y];
            }
        }
    }
    
    public function finalize()
    {
        $this->_isFinalized = true;
        for ($x = 0; $x < count($this->dataArray); $x++) {
            for ($y = 0; $y < count($this->dataArray[$x]); $y++) {
                $this->dataArray[$x][$y] = $this->dataArray[$x][$y] / $this->_sampleSize;
            }
        }
    }
    
    protected function _findCellColor($x, $y)
    {
        if ($this->dataArray[$x][$y] === 0) {
            return "white"; //If it's none no need to bother with calculations, just return white...
        }
        if ($this->dataArray[$x][$y] == 1) {
            return "black";
        }
        //If it's something else no need to bother with calculations, oh,... I have to...
        //FIXME: I should calculate the colors where 0 is 256 and 1 is 0, so the they should be between...
        $val = $this->dataArray[$x][$y] * 256;
        $rgb = dechex(256 - $val);
        if (strlen($rgb) === 1) {
            $rgb = "0" . $rgb; //Bad fix to fix the problem which occurs in RGB 'c' was interpreted as 'cc' however it should have been interpreted as '0c'!!!!
        }
        return "#" . $rgb . $rgb . $rgb;
    }
    
    public function draw()
    {
        echo '<div class="meanContainer' . $this->numberValue . '">Total Number of Samples: ' . $this->_sampleSize;
        parent::draw();
        echo '</div>';
    }
}
