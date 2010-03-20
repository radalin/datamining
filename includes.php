<?php


/**
 * Reads cubes from a file and creates appropriate DataCube objects...
 */
class DataCubeReader
{
    private $_fileName;
    
    private $_cubeSize;
    
    public function __construct($fileName = "testcom.txt", $cubeSize = 16)
    {
        $this->_fileName = $fileName;
        $this->_cubeSize = $cubeSize;
    }
    
    /**
     *
     * @return array<DataCubeList>
     */
    public function read()
    {
        //Read it line by line...
        $fileContents = split("\n", file_get_contents($this->_fileName));
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
            if ($i > $this->_cubeSize * 100) {
                break;
            }
        }
        return $cubeList;
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
    
    private function _matchWith(DataCube $cube)
    {
        //Return the heuristic number...
    }
    
    public function matchWithAll(DataCubeList $cubeList)
    {
        return $this->matchWithNearest($cubeList, 1); //Match with the closes match only...
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
    
    public function __set($key, $value)
    {
        $attributeName = "_" . $key;
        $this->$attributeName = $value;
        return $this;
    }
    
    public function __get($key)
    {
        $attributeName = "_" . $key;
        return $this->$attributeName;
    }
    
    public function draw()
    {
        echo '<div class="dataCube"> This Number is: ' . $this->_numberValue . '<br />';

        for ($i = 0; $i < count($this->_dataArray); $i++) {
            echo '<div class="dataCubeRow">';
            for ($j = 0; $j < count($this->_dataArray[$i]); $j++) {
                $color = $this->_findCellColor($i, $j);
                $this->_drawCellWithColor($color);
            }
            echo '</div>';
        }
        echo '</div><br />';
    }
    
    protected function _drawCellWithColor($color)
    {
        echo '<div class="dataCubeCell" style="background-color:' . $color . '">&nbsp;</div>';
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
        $val = (int) $this->_dataArray[$x][$y] * 256 / $this->_sampleSize;
        $rgb = dechex(256 - $val);
        return "#" . $rgb . $rgb . $rgb;
    }
}