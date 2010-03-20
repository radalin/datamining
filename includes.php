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
            if ($i > $this->_cubeSize * 20) {
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
     * @DataCube
     */
    private $_meanDataCube = null;
    
    public function __construct($number, $cubes)
    {
        $this->_numberValueForSamples = $number;
        $this->_cubeArray = $cubes;
    }
    
    public function calculateMeanDataCube()
    {
        $this->_meanDataCube = $this->_cubeArray[0];
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
        for ($i = 0; $i < $this->count(); $i++) {
            $this->_cubeArray[$i]->draw();
        }
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
    private $_dataArray = array();
    
    private $_numberValue = null;
    
    public function __construct($number, $array)
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
        
    }
    
    public function matchWithAll(DataCubeList $cubeList)
    {
        $heuristics = array();
        for ($i = 0; $i < $cubeList->count(); $i++) {
            $heuristics[$i] = $this->matchWith($cubeArray->get($i)); //Calculate the heuristics for the DataCube thingy...
        }
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
    private $_sampleSize = 0;
    
    public function __construct($number, $array, $sampleSize)
    {
        parent::__construct($number, $array);
        $this->_sampleSize = $sampleSize;
    }
    
    protected function _findCellColor($x, $y)
    {
        //Color of the cell will be different according to the value there is
        //Not just a black and white picture but a gradient color where black is max sample size and white is zero 
        // and the rest in between... Yeah there will be some RGB incresing thing...
    }
}