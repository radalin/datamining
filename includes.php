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
        $cubeList = array();
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
            $cubeList[] = clone $cube;
            $cube = null;
        }
        return $cubeList;
    }
}

class CubeMatcher
{
    private $_reader;
    
    /**
     * @var DataCubeList
     */
    private $_dataCubeList;
    
    public function __construct($trainFile, $testFile, $cubeSize)
    {
        $this->_reader = new DataCubeReader($cubeSize, $trainFile, $testFile);
    }
    
    public function trainMe()
    {
        $this->_dataCubeList = $this->_reader->readTrainingData();
//        var_dump($this->_dataCubeList);
    }
    
    public function drawMeans()
    {
        for ($i = 0; $i < count($this->_dataCubeList); $i++) {
            $this->_dataCubeList[$i]->drawMean();
        }
    }
    
    public function matchTestDataWithAll()
    {
        
    }
    
    public function matchTestDataWithMeans()
    {
        if ($this->_dataCubeList == null) {
            $this->trainMe();
        }
        $testCubes = $this->_reader->readTestData();
        $testCount = count($testCubes);
        $failure = 0;
        $values = array();
        for ($i = 0; $i < $testCount; $i++) {
            //Match with all means...
            for ($j = 0; $j < count($this->_dataCubeList); $j++) {
                $values[$j] = $testCubes[$i]->matchWith($this->_dataCubeList[$j]->getMean());
            }
            arsort($values); //Sort it by reverse...
            foreach ($values as $key => $val) {
                if ($key == $testCubes[$i]->numberValue) {
                    echo "Success, we have for {$testCubes[$i]->numberValue} and {$key}.<br>";
                } else {
                    echo "Confused {$testCubes[$i]->numberValue} with $key<br />";
                    $failure++;
                }
                break;
            }
        }
        echo "Total Failure: " . $failure;
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
    }
    
    //Below is the less important stuff some simple getters and setters and drawers etc...
    
    public function get($index)
    {
        return $this->_cubeArray[$i];
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
    protected $_dataArray = array();
    
    protected $_numberValue = null;
    
    public function __construct($number = null, $array = array())
    {
        $this->_dataArray = $array;
        $this->_numberValue = $number;
    }
        
    protected function _findCellColor($x, $y)
    {
        //Every 1 (one) is black and every 0 (zero) is white
        if ($this->_dataArray[$x][$y] == 1) {
            return "#000000"; //Return black...
        }
        return "#FFFFFF"; //Return white...
    }
    
    public function matchWith(DataCube $cube)
    {
        //Return the heuristic number...
        //The mathic numbers gives us a point, higher points means, higher matching the one with highest match num is the winner...
        $heuristic = 0;
        for ($i = 0; $i < count($this->_dataArray); $i++) {
            for ($j = 0; $j < count($this->_dataArray[$i]); $j++) {
                //var_dump($this->_dataArray[$i][$j], $cube->dataArray[$i][$j]);
                if ($this->_dataArray[$i][$j] > 0 && $cube->dataArray[$i][$j] > 0) { //If there is a value in both of them...
                    $heuristic += $cube->dataArray[$i][$j] * $cube->dataArray[$i][$j];
                }
                if ($this->_dataArray[$i][$j] == 0 && $cube->dataArray[$i][$j] == 0) { //If zeros are matching...
                    $heuristic += 30 * 30;
                }
                if ($this->_dataArray[$i][$j] == 0 && $cube->dataArray[$i][$j] < 30) { //If the cube is zero and mean is gray...
                    $heuristic += 30 * 30;
                }
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
            $heuristics[$i] = $this->matchWith($cubeArray->get($i)); //Calculate the heuristics for the DataCube thingy...
        }
        //Now match with $k nearest neighbors...
        //Sort the array as decreasing while keeping the index values (key-value associations) where I will get to the DataCube from $cubeList...
        $heuristics = asort($heuristics);
        //So here is the easy part, what are the values of top $k elements...
        for ($i = 0; $i < 10; $i++) {
            $numbers[$i] = 0; //A baaaaaaaad code comes here....
        }
        for ($i = 0; $i <= $k; $i++) {
            var_dump($cubeArray->get($i)->numberValue, $heuristics[$i]);
            $numbers[$cubeArray->get($i)->numberValue]++;
        }
        //Now sort the numbers array without maintaining the key-value association
        ksort($numbers);
        var_dump($numbers);
        return $numbers[0]; //Returns the number with highest value...
    }
  
    //The below is the less important stuff, the drawing implementation etc...
    
    public function __get($key)
    {
        if ($key == "dataArray" || $key == "numberValue") {
            $attributeName = "_" . $key;
            return $this->$attributeName;
        }
        return "";
    }
    
    public function draw()
    {
        echo '<div class="dataCube"> This Number is: ' . $this->_numberValue . '<br />';
        for ($i = 0; $i < count($this->_dataArray); $i++) {
            echo '<div class="dataCubeRow">';
            for ($j = 0; $j < count($this->_dataArray[$i]); $j++) {
                $color = $this->_findCellColor($i, $j);
                $this->_drawCellWithColor($color, $this->_dataArray[$i][$j]);
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
    
    public function __construct($number, $array)
    {
        parent::__construct($number, $array);
        $this->_sampleSize = 1;
    }
    
    public function addToSum(DataCube $cube)
    {
        $this->_sampleSize++; //Increase the sample size...
        for ($x = 0; $x < count($this->_dataArray); $x++) {
            for ($y = 0; $y < count($this->_dataArray[$x]); $y++) {
                $this->_dataArray[$x][$y] = $this->_dataArray[$x][$y] + $cube->dataArray[$x][$y];
            }
        }
    }
    protected function _findCellColor($x, $y)
    {
        if ($this->_dataArray[$x][$y] === 0) {
            return "white"; //If it's none no need to bother with calculations, just return white...
        }
        if ($this->_dataArray[$x][$y] === $this->_sampleSize) {
            return "black"; //If it's equal to sampleSize no need to bother with calculations, just return black...
        }
        //If it's something else no need to bother with calculations, oh,... I have to...
        $val = (int) $this->_dataArray[$x][$y] * 256 / $this->_sampleSize;
        $rgb = dechex(256 - $val);
        if (strlen($rgb) === 1) {
            $rgb = "0" . $rgb; //Bad fix to fix the problem which occurs in RGB 'c' was interpreted as 'cc' however it should have been interpreted as '0c'!!!!
        }
        return "#" . $rgb . $rgb . $rgb;
    }
    
    public function draw()
    {
        echo '<div class="' . $this->_numberValue . '">Total Number of Samples: ' . $this->_sampleSize;
        parent::draw();
        echo '</div>';
    }
}